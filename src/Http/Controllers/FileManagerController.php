<?php

namespace UniversalFileManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use UniversalFileManager\Models\Media;
use Intervention\Image\Facades\Image;

class FileManagerController extends Controller
{
    /**
     * List media items for a given folder.
     */
    public function index(Request $request)
    {
        $query = Media::where('parent_id', $request->input('parent_id'));

        if ($search = $request->input('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $items = $query->orderBy('is_folder', 'desc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($item) {
                $item->url = $item->is_folder ? null : $this->getUrl($item->path);
                return $item;
            });

        return response()->json(['data' => $items]);
    }

    /**
     * Get breadcrumbs for current folder.
     */
    public function breadcrumbs(Request $request)
    {
        $breadcrumbs = [];
        $current = Media::find($request->input('parent_id'));

        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }

        return response()->json(['data' => $breadcrumbs]);
    }

    /**
     * Upload files.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:20480',
            'parent_id' => 'nullable|integer|exists:media,id',
        ]);

        $uploaded = [];
        $disk = config('ufm.storage_disk', 'public');

        foreach ($request->file('files', []) as $file) {
            $filename = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $path = $file->store('uploads', $disk);

            $media = Media::create([
                'name' => $filename,
                'file_name' => basename($path),
                'mime_type' => $mimeType,
                'path' => $path,
                'disk' => $disk,
                'size' => $file->getSize(),
                'is_folder' => false,
                'parent_id' => $request->input('parent_id'),
            ]);

            if (str_starts_with($mimeType, 'image/')) {
                $this->processImage($media);
            }

            $uploaded[] = $media;
        }

        return response()->json([
            'message' => count($uploaded) . ' file(s) uploaded successfully.',
            'data' => $uploaded,
        ]);
    }

    /**
     * Create a new folder.
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:media,id',
        ]);

        $folder = Media::create([
            'name' => $request->input('name'),
            'is_folder' => true,
            'parent_id' => $request->input('parent_id'),
        ]);

        return response()->json(['message' => 'Folder created.', 'data' => $folder]);
    }

    /**
     * Delete one or many media items.
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['message' => 'No items specified.'], 422);
        }

        foreach ($ids as $id) {
            $media = Media::find($id);
            if (!$media) continue;
            $this->performDelete($media);
        }

        return response()->json(['message' => count($ids) . ' item(s) deleted.']);
    }

    /**
     * Copy items to clipboard (returns clipboard state).
     */
    public function copy(Request $request)
    {
        $request->validate(['ids' => 'required|array']);

        return response()->json([
            'clipboardIds' => $request->input('ids'),
            'clipboardAction' => 'copy',
        ]);
    }

    /**
     * Move items to clipboard (returns clipboard state).
     */
    public function move(Request $request)
    {
        $request->validate(['ids' => 'required|array']);

        return response()->json([
            'clipboardIds' => $request->input('ids'),
            'clipboardAction' => 'move',
        ]);
    }

    /**
     * Paste clipboard items into current folder.
     */
    public function paste(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'action' => 'required|in:copy,move',
            'parent_id' => 'nullable|integer',
        ]);

        $parentId = $request->input('parent_id');

        foreach ($request->input('ids') as $id) {
            $item = Media::find($id);
            if (!$item) continue;

            if ($request->input('action') === 'move') {
                $item->update(['parent_id' => $parentId]);
            } else {
                $this->duplicateMedia($item, $parentId);
            }
        }

        return response()->json(['message' => 'Paste completed.']);
    }

    /**
     * Download a media file.
     */
    public function download($id)
    {
        $media = Media::findOrFail($id);

        if ($media->is_folder) {
            return response()->json(['message' => 'Cannot download folders.'], 422);
        }

        return Storage::disk($media->disk)->download($media->path, $media->name);
    }

    /**
     * Select/toggle media item (for API state tracking).
     */
    public function select(Request $request)
    {
        // Selection is managed client-side for API stacks
        return response()->json(['message' => 'OK']);
    }

    /**
     * Move item to a specific folder (drag and drop).
     */
    public function moveToFolder(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'target_folder_id' => 'nullable|integer',
        ]);

        $itemId = $request->input('item_id');
        $targetFolderId = $request->input('target_folder_id');

        if ($itemId == $targetFolderId) {
            return response()->json(['message' => 'Cannot move item into itself.'], 422);
        }

        $item = Media::findOrFail($itemId);

        if ($item->is_folder) {
            $current = Media::find($targetFolderId);
            while ($current) {
                if ($current->id == $itemId) {
                    return response()->json(['message' => 'Cannot move folder into its own child.'], 422);
                }
                $current = $current->parent;
            }
        }

        $item->update(['parent_id' => $targetFolderId]);

        return response()->json(['message' => 'Item moved.', 'data' => $item]);
    }

    // ─── Private Helpers ─────────────────────────────────────

    protected function processImage(Media $media)
    {
        $image = Image::make(Storage::disk($media->disk)->path($media->path));

        if ($image->width() > 1920) {
            $image->resize(1920, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $image->save(null, 80);

        $media->update([
            'width' => $image->width(),
            'height' => $image->height(),
            'size' => Storage::disk($media->disk)->size($media->path),
        ]);
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

    protected function duplicateMedia(Media $item, $parentId)
    {
        $newItem = $item->replicate();
        $newItem->parent_id = $parentId;

        if (!$item->is_folder) {
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

    protected function getUrl($path)
    {
        if (!$path) return null;
        $url = Storage::disk(config('ufm.storage_disk', 'public'))->url($path);

        $parsed = parse_url($url);
        if (isset($parsed['host']) && ($parsed['host'] === 'localhost' || $parsed['host'] === '127.0.0.1')) {
            $currentPort = request()->getPort();
            if (isset($parsed['port']) && $parsed['port'] != $currentPort) {
                $url = str_replace(':' . $parsed['port'], ':' . $currentPort, $url);
            } elseif (!isset($parsed['port']) && $currentPort != 80) {
                $url = str_replace($parsed['host'], $parsed['host'] . ':' . $currentPort, $url);
            }
        }

        return $url;
    }
}
