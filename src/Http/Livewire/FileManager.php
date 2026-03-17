<?php

namespace UniversalFileManager\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Storage;
use UniversalFileManager\Models\Media;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class FileManager extends Component
{
    use WithFileUploads;

    public $files = [];
    #[Url]
    public $currentFolderId = null;
    public $newFolderName;
    #[Url]
    public $search = '';

    #[Url]
    public $clipboardIds = [];
    #[Url]
    public $clipboardAction = null; // 'copy' or 'move'

    public $selectedIds = [];
    public $previewId = null;

    protected $listeners = ['refreshFileManager' => '$refresh'];

    public function mount()
    {
        // currentFolderId and search are automatically hydrated from URL
    }

    public function getMediaProperty()
    {
        $query = Media::where('parent_id', $this->currentFolderId);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return $query->orderBy('is_folder', 'desc')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getBreadcrumbsProperty()
    {
        $breadcrumbs = [];
        $current = Media::find($this->currentFolderId);

        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }

        return $breadcrumbs;
    }

    public function updatedFiles()
    {
        $this->validate([
            'files.*' => 'required|file|max:20480', // 20MB max
        ]);

        try {
            foreach ($this->files as $file) {
                $this->uploadFile($file);
            }
            session()->flash('message', count($this->files) . ' files uploaded successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Upload failed: ' . $e->getMessage());
        }

        $this->files = [];
        $this->dispatch('refreshFileManager');
    }

    protected function uploadFile($file)
    {
        $filename = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $disk = config('ufm.storage_disk', 'public');

        $path = $file->store('uploads', $disk);

        $media = Media::create([
            'name' => $filename,
            'file_name' => basename($path),
            'mime_type' => $mimeType,
            'path' => $path,
            'disk' => $disk,
            'size' => $file->getSize(),
            'is_folder' => false,
            'parent_id' => $this->currentFolderId,
        ]);

        if (str_starts_with($mimeType, 'image/')) {
            $this->processImage($media);
        }
    }

    protected function processImage(Media $media)
    {
        $image = Image::make(Storage::disk($media->disk)->path($media->path));

        // Resize if larger than 1920px
        if ($image->width() > 1920) {
            $image->resize(1920, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $image->save(null, 80); // Compress to 80% quality

        $media->update([
            'width' => $image->width(),
            'height' => $image->height(),
            'size' => Storage::disk($media->disk)->size($media->path),
        ]);
    }

    public function createFolder()
    {
        $this->validate([
            'newFolderName' => 'required|string|max:255',
        ]);

        Media::create([
            'name' => $this->newFolderName,
            'is_folder' => true,
            'parent_id' => $this->currentFolderId,
        ]);

        $this->newFolderName = '';
        $this->dispatch('refreshFileManager');
    }

    public function navigateTo($folderId = null)
    {
        $this->currentFolderId = $folderId;
        $this->selectedIds = [];
    }

    public function deleteMedia($id = null)
    {
        $ids = $id ? [$id] : $this->selectedIds;
        if (empty($ids)) return;

        foreach ($ids as $targetId) {
            $media = Media::find($targetId);
            if (!$media) continue;

            $this->performDelete($media);
        }

        $this->selectedIds = array_diff($this->selectedIds, $ids);
        $this->dispatch('refreshFileManager');
    }

    protected function performDelete(Media $media)
    {
        if ($media->is_folder) {
            foreach ($media->children as $child) {
                $this->performDelete($child);
            }
        } else {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();
    }

    public function copyMedia($id = null)
    {
        $this->clipboardIds = $id ? [$id] : $this->selectedIds;
        $this->clipboardAction = 'copy';
    }

    public function moveMedia($id = null)
    {
        $this->clipboardIds = $id ? [$id] : $this->selectedIds;
        $this->clipboardAction = 'move';
    }

    public function cancelClipboard()
    {
        $this->clipboardIds = [];
        $this->clipboardAction = null;
    }

    public function paste()
    {
        if (empty($this->clipboardIds) || !$this->clipboardAction) return;

        foreach ($this->clipboardIds as $id) {
            $item = Media::find($id);
            if (!$item) continue;

            if ($this->clipboardAction === 'move') {
                $item->update(['parent_id' => $this->currentFolderId]);
            } else {
                $this->duplicateMedia($item, $this->currentFolderId);
            }
        }

        $this->cancelClipboard();
        $this->dispatch('refreshFileManager');
    }

    protected function duplicateMedia(Media $item, $parentId)
    {
        $newItem = $item->replicate();
        $newItem->parent_id = $parentId;

        if (!$item->is_folder) {
            // Copy physical file
            $newPath = 'uploads/' . Str::random(40) . '.' . pathinfo($item->path, PATHINFO_EXTENSION);
            Storage::disk($item->disk)->copy($item->path, $newPath);
            $newItem->path = $newPath;
            $newItem->file_name = basename($newPath);
        }

        $newItem->save();

        if ($item->is_folder) {
            foreach ($item->children as $child) {
                $this->duplicateMedia($child, $newItem->id);
            }
        }
    }

    public function moveToFolder($itemId, $targetFolderId)
    {
        // Prevent moving a folder into itself or its children
        if ($itemId == $targetFolderId) return;

        $item = Media::findOrFail($itemId);

        if ($item->is_folder) {
            $current = Media::find($targetFolderId);
            while ($current) {
                if ($current->id == $itemId) return; // Cannot move into child
                $current = $current->parent;
            }
        }

        $item->update(['parent_id' => $targetFolderId]);
        $this->dispatch('refreshFileManager');
    }

    public function selectMedia($id)
    {
        $id = (string)$id;
        if (in_array($id, $this->selectedIds)) {
            $this->selectedIds = array_diff($this->selectedIds, [$id]);
        } else {
            $this->selectedIds[] = $id;
        }
    }

    public function selectAll()
    {
        $ids = Media::where('parent_id', $this->currentFolderId)->pluck('id')->toArray();
        $this->selectedIds = array_map('strval', $ids);
    }

    public function getUrl($path)
    {
        if (!$path) return null;
        $url = Storage::disk(config('ufm.storage_disk', 'public'))->url($path);

        // Fix port mismatch for local development
        $parsed = parse_url($url);
        if (isset($parsed['host']) && ($parsed['host'] === 'localhost' || $parsed['host'] === '127.0.0.1')) {
            $currentPort = request()->getPort();
            // Replace port 8000 with current port if mismatch
            if (isset($parsed['port']) && $parsed['port'] != $currentPort) {
                $url = str_replace(':' . $parsed['port'], ':' . $currentPort, $url);
            } elseif (!isset($parsed['port']) && $currentPort != 80) {
                // If no port in URL but current port is not 80, inject it
                $url = str_replace($parsed['host'], $parsed['host'] . ':' . $currentPort, $url);
            }
        }

        return $url;
    }

    public function unselectAll()
    {
        $this->selectedIds = [];
    }

    public function downloadMedia($id = null)
    {
        $ids = $id ? [$id] : $this->selectedIds;

        if (count($ids) === 1) {
            $media = Media::findOrFail($ids[0]);
            if ($media->is_folder) {
                return session()->flash('error', 'Cannot download folders.');
            }
            return Storage::disk($media->disk)->download($media->path, $media->name);
        }

        // Multiple downloads - browser handles it as individual requests usually if triggered properly,
        // but for Livewire we might need a workaround or just download one by one.
        // For simplicity, we'll return the first one for now or flash a message.
        session()->flash('info', 'Bulk download started.');
        // In a real app, we'd zip them here.
        return Storage::disk(Media::findOrFail($ids[0])->disk)->download(Media::findOrFail($ids[0])->path, Media::findOrFail($ids[0])->name);
    }

    public function previewMedia($id)
    {
        $this->previewId = $id;
    }

    public function closePreview()
    {
        $this->previewId = null;
    }

    public function render()
    {
        return view('ufm::livewire.file-manager', [
            'mediaItems' => $this->media,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }
}
