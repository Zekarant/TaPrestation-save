<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminContentController extends Controller
{
    private const PUBLIC_PATH_BLOCKED_PATTERN = '/(^|\/)\.\.(\/|$)/';

    /**
     * 32. Gestion des pages statiques
     */
    public function pages()
    {
        $pages = DB::table('static_pages')->orderBy('title')->get();
        return view('admin.content.pages', compact('pages'));
    }

    public function createPage()
    {
        return view('admin.content.pages-create');
    }

    public function storePage(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:static_pages',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        DB::table('static_pages')->insert([
            'title' => $request->title,
            'slug' => Str::slug($request->slug),
            'content' => $request->content,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'is_published' => $request->has('is_published'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.content.pages')->with('success', 'Page créée avec succès.');
    }

    public function editPage($id)
    {
        $page = DB::table('static_pages')->where('id', $id)->first();
        return view('admin.content.pages-edit', compact('page'));
    }

    public function updatePage(Request $request, $id)
    {
        DB::table('static_pages')->where('id', $id)->update([
            'title' => $request->title,
            'slug' => Str::slug($request->slug),
            'content' => $request->content,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'is_published' => $request->has('is_published'),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Page mise à jour.');
    }

    public function deletePage($id)
    {
        DB::table('static_pages')->where('id', $id)->delete();
        return back()->with('success', 'Page supprimée.');
    }

    /**
     * 33. Gestion des FAQ
     */
    public function faqs()
    {
        $faqs = DB::table('faqs')->orderBy('order')->get();
        $categories = DB::table('faq_categories')->get();
        return view('admin.content.faqs', compact('faqs', 'categories'));
    }

    public function storeFaq(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'category_id' => 'nullable|exists:faq_categories,id',
        ]);

        $maxOrder = DB::table('faqs')->max('order') ?? 0;

        DB::table('faqs')->insert([
            'question' => $request->question,
            'answer' => $request->answer,
            'category_id' => $request->category_id,
            'order' => $maxOrder + 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'FAQ ajoutée.');
    }

    public function updateFaq(Request $request, $id)
    {
        DB::table('faqs')->where('id', $id)->update([
            'question' => $request->question,
            'answer' => $request->answer,
            'category_id' => $request->category_id,
            'is_active' => $request->has('is_active'),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'FAQ mise à jour.');
    }

    public function deleteFaq($id)
    {
        DB::table('faqs')->where('id', $id)->delete();
        return back()->with('success', 'FAQ supprimée.');
    }

    public function reorderFaqs(Request $request)
    {
        foreach ($request->order as $index => $id) {
            DB::table('faqs')->where('id', $id)->update(['order' => $index]);
        }
        return response()->json(['success' => true]);
    }

    /**
     * 34. Gestion des bannières/sliders
     */
    public function banners()
    {
        $banners = DB::table('banners')->orderBy('order')->get();
        return view('admin.content.banners', compact('banners'));
    }

    public function storeBanner(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'link' => 'nullable|url',
            'position' => 'required|string',
        ]);

        $imagePath = $request->file('image')->store('banners', 'public');
        $maxOrder = DB::table('banners')->where('position', $request->position)->max('order') ?? 0;

        DB::table('banners')->insert([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'image' => $imagePath,
            'link' => $request->link,
            'position' => $request->position,
            'order' => $maxOrder + 1,
            'is_active' => true,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Bannière ajoutée.');
    }

    public function updateBanner(Request $request, $id)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = [
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'link' => $request->link,
            'position' => $request->position,
            'is_active' => $request->has('is_active'),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'updated_at' => now(),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        DB::table('banners')->where('id', $id)->update($data);
        return back()->with('success', 'Bannière mise à jour.');
    }

    public function deleteBanner($id)
    {
        $banner = DB::table('banners')->where('id', $id)->first();
        if ($banner && $banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        DB::table('banners')->where('id', $id)->delete();
        return back()->with('success', 'Bannière supprimée.');
    }

    /**
     * 35. Gestion des témoignages
     */
    public function testimonials()
    {
        $testimonials = DB::table('testimonials')->orderBy('created_at', 'desc')->get();
        return view('admin.content.testimonials', compact('testimonials'));
    }

    public function storeTestimonial(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('testimonials', 'public');
        }

        DB::table('testimonials')->insert([
            'name' => $request->name,
            'role' => $request->role,
            'company' => $request->company,
            'content' => $request->content,
            'rating' => $request->rating,
            'photo' => $photoPath,
            'is_featured' => $request->has('is_featured'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Témoignage ajouté.');
    }

    public function updateTestimonial(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'role' => $request->role,
            'company' => $request->company,
            'content' => $request->content,
            'rating' => $request->rating,
            'is_featured' => $request->has('is_featured'),
            'is_active' => $request->has('is_active'),
            'updated_at' => now(),
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('testimonials', 'public');
        }

        DB::table('testimonials')->where('id', $id)->update($data);
        return back()->with('success', 'Témoignage mis à jour.');
    }

    public function deleteTestimonial($id)
    {
        DB::table('testimonials')->where('id', $id)->delete();
        return back()->with('success', 'Témoignage supprimé.');
    }

    /**
     * 36. Gestion des emails templates
     */
    public function emailTemplates()
    {
        $templates = DB::table('email_templates')->orderBy('name')->get();
        return view('admin.content.email-templates', compact('templates'));
    }

    public function editEmailTemplate($id)
    {
        $template = DB::table('email_templates')->where('id', $id)->first();
        $variables = $this->getTemplateVariables($template->type ?? 'general');
        return view('admin.content.email-templates-edit', compact('template', 'variables'));
    }

    public function updateEmailTemplate(Request $request, $id)
    {
        DB::table('email_templates')->where('id', $id)->update([
            'subject' => $request->subject,
            'body' => $request->body,
            'is_active' => $request->has('is_active'),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Template mis à jour.');
    }

    public function previewEmailTemplate($id)
    {
        $template = DB::table('email_templates')->where('id', $id)->first();
        $preview = $this->renderEmailPreview($template);
        return view('admin.content.email-templates-preview', compact('template', 'preview'));
    }

    /**
     * 37. Gestion des médias/fichiers
     */
    public function mediaLibrary(Request $request)
    {
        $path = $this->normalizePublicRelativePath($request->get('path', ''), true);
        if ($path === null) {
            abort(404);
        }

        $fullPath = $this->resolvePublicAbsolutePath($path);

        $files = [];
        $folders = [];

        if ($fullPath && is_dir($fullPath)) {
            $items = scandir($fullPath);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;

                $itemPath = $fullPath . '/' . $item;
                if (is_dir($itemPath)) {
                    $folderPath = $path ? $path . '/' . $item : $item;
                    $folders[] = [
                        'name' => $item,
                        'path' => $folderPath,
                    ];
                } else {
                    $filePath = $path ? $path . '/' . $item : $item;
                    $files[] = [
                        'name' => $item,
                        'path' => $filePath,
                        'url' => Storage::url($filePath),
                        'size' => filesize($itemPath),
                        'modified' => date('Y-m-d H:i:s', filemtime($itemPath)),
                        'type' => mime_content_type($itemPath),
                    ];
                }
            }
        }

        return view('admin.content.media-library', compact('files', 'folders', 'path'));
    }

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,csv,mp4,mp3,zip', // 10MB max, types restreints (audit 1.7)
        ]);

        $path = $this->normalizePublicRelativePath($request->get('path', ''), true);
        if ($path === null) {
            return back()->with('error', 'Chemin de dossier invalide.');
        }

        $file = $request->file('file');
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '_' . time() . '.' . $file->getClientOriginalExtension();

        $targetDir = $path !== '' ? 'public/' . $path : 'public';
        $file->storeAs($targetDir, $filename);

        return back()->with('success', 'Fichier uploadé.');
    }

    public function deleteMedia(Request $request)
    {
        $path = $this->normalizePublicRelativePath($request->get('path'), false);
        if ($path === null) {
            return back()->with('error', 'Chemin de fichier invalide.');
        }

        Storage::disk('public')->delete($path);
        return back()->with('success', 'Fichier supprimé.');
    }

    public function createFolder(Request $request)
    {
        $path = $this->normalizePublicRelativePath($request->get('path', ''), true);
        if ($path === null) {
            return back()->with('error', 'Chemin de dossier invalide.');
        }

        $name = Str::slug($request->name);
        if ($name === '') {
            return back()->with('error', 'Nom de dossier invalide.');
        }

        $targetPath = $path ? $path . '/' . $name : $name;
        $targetPath = $this->normalizePublicRelativePath($targetPath, false);
        if ($targetPath === null) {
            return back()->with('error', 'Impossible de créer ce dossier.');
        }

        Storage::disk('public')->makeDirectory($targetPath);
        return back()->with('success', 'Dossier créé.');
    }

    // Méthodes privées
    private function normalizePublicRelativePath(?string $path, bool $allowEmpty): ?string
    {
        $value = str_replace('\\', '/', trim((string) $path));
        $value = ltrim($value, '/');
        $value = preg_replace('#/+#', '/', $value) ?? '';

        if ($value === '') {
            return $allowEmpty ? '' : null;
        }

        if (
            str_contains($value, "\0")
            || preg_match(self::PUBLIC_PATH_BLOCKED_PATTERN, $value)
            || str_starts_with($value, '..')
        ) {
            return null;
        }

        $root = realpath(storage_path('app/public'));
        if ($root === false) {
            return null;
        }

        $candidate = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $value);
        $candidateReal = realpath($candidate);
        $rootPrefix = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if ($candidateReal !== false) {
            if ($candidateReal !== $root && !str_starts_with($candidateReal, $rootPrefix)) {
                return null;
            }
            return trim(str_replace('\\', '/', $value), '/');
        }

        $parentReal = realpath(dirname($candidate));
        if ($parentReal === false) {
            return null;
        }
        if ($parentReal !== $root && !str_starts_with($parentReal, $rootPrefix)) {
            return null;
        }

        return trim(str_replace('\\', '/', $value), '/');
    }

    private function resolvePublicAbsolutePath(string $relativePath): ?string
    {
        $root = realpath(storage_path('app/public'));
        if ($root === false) {
            return null;
        }

        if ($relativePath === '') {
            return $root;
        }

        $candidate = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $candidateReal = realpath($candidate);
        $rootPrefix = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if ($candidateReal === false) {
            return null;
        }

        if ($candidateReal !== $root && !str_starts_with($candidateReal, $rootPrefix)) {
            return null;
        }

        return $candidateReal;
    }

    private function getTemplateVariables($type)
    {
        $variables = [
            'general' => ['{{name}}', '{{email}}', '{{site_name}}'],
            'booking' => ['{{name}}', '{{service}}', '{{date}}', '{{price}}', '{{prestataire}}'],
            'payment' => ['{{name}}', '{{amount}}', '{{transaction_id}}', '{{date}}'],
            'notification' => ['{{name}}', '{{message}}', '{{action_url}}'],
        ];

        return $variables[$type] ?? $variables['general'];
    }

    private function renderEmailPreview($template)
    {
        $body = $template->body ?? '';
        $replacements = [
            '{{name}}' => 'John Doe',
            '{{email}}' => 'john@example.com',
            '{{site_name}}' => config('app.name'),
            '{{service}}' => 'Service Exemple',
            '{{date}}' => now()->format('d/m/Y'),
            '{{price}}' => '100 €',
            '{{prestataire}}' => 'Prestataire Exemple',
            '{{amount}}' => '100 €',
            '{{transaction_id}}' => 'TXN-12345',
            '{{message}}' => 'Ceci est un message de test.',
            '{{action_url}}' => url('/'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $body);
    }
}
