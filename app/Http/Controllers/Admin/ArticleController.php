<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Models\Article;
use App\Services\SitemapService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::latest()
            ->when(request('search'), function ($query) {
                $query->where('title', 'like', '%' . request('search') . '%')
                    ->orWhere('excerpt', 'like', '%' . request('search') . '%')
                    ->orWhere('content', 'like', '%' . request('search') . '%');
            })
            ->paginate(10);
        return view('admin.articles.index', compact('articles'));
    }

    public function show(Article $article)
    {
        return view('articles.show', compact('article'));
    }

    public function create()
    {
        return view('admin.articles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['title', 'excerpt', 'content', 'category']);

        $data['slug'] = Str::slug($request->title);

        $originalSlug = $data['slug'];
        $counter = 1;
        while (Article::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter++;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeArticleImage($request->file('image'));
        }

        [$data['meta_title'], $data['meta_description']] = $this->generateMetaFields(
            $request->title,
            $request->content
        );

        Article::create($data);

        $this->regenerateSitemap();

        return redirect()->route('admin.articles.index')->with('success', 'Article created successfully!');
    }

    public function edit($id)
    {
        $article = Article::findOrFail($id);
        return view('admin.articles.edit', compact('article'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'excerpt'  => 'nullable|string|max:500',
            'content'  => 'required|string',
            'category' => 'nullable|string|max:100',
            'image'    => 'nullable|image|max:2048',
        ]);

        $article = Article::findOrFail($id);

        $data = $request->only(['title', 'excerpt', 'content', 'category']);

        if ($article->title !== $request->title) {
            $data['slug'] = Str::slug($request->title);

            $originalSlug = $data['slug'];
            $counter = 1;
            while (Article::where('slug', $data['slug'])->where('id', '!=', $article->id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter++;
            }
        }

        if ($request->hasFile('image')) {
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $data['image'] = $this->storeArticleImage($request->file('image'));
        }

        [$data['meta_title'], $data['meta_description']] = $this->generateMetaFields(
            $request->title,
            $request->content
        );

        $article->update($data);

        $this->regenerateSitemap();

        return redirect()->route('admin.articles.index')->with('success', 'Article updated successfully!');
    }


    public function destroy($id)
    {
        $article = Article::findOrFail($id);

        if ($article->image && Storage::disk('public')->exists($article->image)) {
            Storage::disk('public')->delete($article->image);
        }

        $article->delete();

        $this->regenerateSitemap();

        return redirect()->route('admin.articles.index')->with('success', 'Article deleted successfully!');
    }

    public function togglePublish($id)
    {
        return redirect()->route('admin.articles.index')
            ->with('info', 'Status artikel sudah dihapus.');
    }

    public function bulkPublish(Request $request)
    {
        return redirect()->route('admin.articles.index')
            ->with('info', 'Status artikel sudah dihapus.');
    }

    private function generateMetaFields(string $title, string $content): array
    {
        $metaTitle = Str::limit(trim(strip_tags($title)), 60, '');
        $plainContent = trim(preg_replace('/\s+/', ' ', strip_tags($content)) ?? '');
        $metaDescription = Str::limit($plainContent, 160, '');

        return [$metaTitle, $metaDescription];
    }

    private function regenerateSitemap(): void
    {
        try {
            Cache::forget('home_articles');
            Cache::increment('blog_cache_version');

            app(SitemapService::class)->generate();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function storeArticleImage(UploadedFile $file): string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecreatetruecolor')) {
            return $file->store('articles', 'public');
        }

        try {
            $rawContent = file_get_contents($file->getRealPath());
            $sourceImage = $rawContent ? \imagecreatefromstring($rawContent) : false;

            if (! $sourceImage) {
                return $file->store('articles', 'public');
            }

            $sourceWidth = \imagesx($sourceImage);
            $sourceHeight = \imagesy($sourceImage);

            // Max width 1200px
            $targetWidth = min($sourceWidth, 1200);
            $ratio = $targetWidth / $sourceWidth;
            $targetHeight = (int) round($sourceHeight * $ratio);

            $targetImage = \imagecreatetruecolor($targetWidth, $targetHeight);

            $white = \imagecolorallocate($targetImage, 255, 255, 255);
            \imagefill($targetImage, 0, 0, $white);

            \imagecopyresampled(
                $targetImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $targetWidth,
                $targetHeight,
                $sourceWidth,
                $sourceHeight
            );

            ob_start();
            \imagejpeg($targetImage, null, 80);
            $processedImage = ob_get_clean();

            \imagedestroy($sourceImage);
            \imagedestroy($targetImage);

            if ($processedImage === false || $processedImage === '') {
                return $file->store('articles', 'public');
            }

            $filename = Str::uuid() . '.jpg';
            $directory = storage_path('app/public/articles');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $fullPath = $directory . '/' . $filename;
            file_put_contents($fullPath, $processedImage);

            return 'articles/' . $filename;
        } catch (\Throwable $e) {
            report($e);
            // Fallback: store directly without Storage facade to avoid finfo dependency
            $filename = Str::uuid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
            $directory = storage_path('app/public/articles');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $file->move($directory, $filename);

            return 'articles/' . $filename;
        }
    }
}
