<?php

namespace UniversalFileManager\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use UniversalFileManager\Models\Media;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class FileManager extends Component
{
    use WithFileUploads;

    public $files = [];
    public $currentFolderId = null;
    public $newFolderName;
    public $search = '';

    protected $listeners = ['refreshFileManager' => '$refresh'];

    public function mount($folderId = null)
    {
        $this->currentFolderId = $folderId;
    }

    public function getMediaProperty()
    {
        return Media::where('parent_id', $this->currentFolderId)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('is_folder', 'desc')
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

        foreach ($this->files as $file) {
            $this->uploadFile($file);
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

    public function navigateTo($folderId)
    {
        $this->currentFolderId = $folderId;
        $this->dispatch('refreshFileManager');
    }

    public function deleteMedia($id)
    {
        $media = Media::findOrFail($id);

        if (!$media->is_folder) {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();
        $this->dispatch('refreshFileManager');
    }

    public function render()
    {
        return view('ufm::livewire.file-manager', [
            'mediaItems' => $this->media,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }
}
