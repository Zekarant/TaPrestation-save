<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVideoFileRequest;
use App\Http\Requests\StoreVideoRequest;
use App\Http\Requests\UpdateVideoRequest;
use App\Jobs\ProcessVideo;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use getID3;


class VideoController extends Controller
{
    public function index()
    {
        // Redirection vers la vue manage qui est plus complète
        return redirect()->route('prestataire.videos.manage');
    }

    public function manage()
    {
        $videos = collect(); // Initialise une collection vide
        if (Auth::user()->prestataire) {
            $videos = Auth::user()->prestataire->videos()->orderBy('created_at', 'desc')->get();
        }
        return view('prestataire.videos.manage', compact('videos'));
    }

    public function create()
    {
        return redirect()->route('prestataire.videos.create.step1');
    }

    public function createStep1()
    {
        return view('prestataire.videos.create-step1');
    }

    public function storeStep1(StoreVideoFileRequest $request)
    {
        $path = null;

        try {
            if ($request->hasFile('video')) {
                $path = $request->file('video')->store('temp_videos', 'public');
            }

            if ($path) {
                // Store video path in session
                $request->session()->put('video_data', [
                    'video_path' => $path,
                ]);

                return redirect()->route('prestataire.videos.create.step2');
            }

            return redirect()->back()->with('error', 'Aucune vidéo n\'a été fournie.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue lors de l’envoi de la vidéo: ' . $e->getMessage());
        }
    }

    public function createStep2()
    {
        // Check if step 1 data exists
        if (!session()->has('video_data') || !isset(session('video_data')['video_path'])) {
            return redirect()->route('prestataire.videos.create.step1')->with('error', 'Veuillez d\'abord importer une vidéo.');
        }

        return view('prestataire.videos.create-step2');
    }

    public function storeStep2(StoreVideoRequest $request)
    {
        // Check if step 1 data exists
        if (!session()->has('video_data') || !isset(session('video_data')['video_path'])) {
            return redirect()->route('prestataire.videos.create.step1')->with('error', 'Veuillez d\'abord importer une vidéo.');
        }

        try {
            // Get video path from session
            $videoPath = session('video_data')['video_path'];

            $video = Auth::user()->prestataire->videos()->create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'video_path' => $videoPath,
                'duration' => 0, // Placeholder, will be updated by ProcessVideo job
                'status' => 'processing', // Initially set status to processing
                'is_public' => true
            ]);

            // Clear session data
            $request->session()->forget('video_data');

            // Dispatch the ProcessVideo job with a delay as per project requirements
            ProcessVideo::dispatch($video)->delay(now()->addSeconds(5));

            return redirect()->route('prestataire.videos.manage')->with('success', 'Vidéo en cours de traitement. Elle sera disponible dans quelques instants.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la création de la vidéo: ' . $e->getMessage());
        }
    }

    public function edit(Video $video)
    {
        $this->authorize('update', $video);
        return view('prestataire.videos.edit', compact('video'));
    }

    public function update(UpdateVideoRequest $request, Video $video)
    {
        $video->update($request->validated());

        return redirect()->route('prestataire.videos.manage')->with('success', 'Vidéo mise à jour avec succès.');
    }

    public function destroy(Video $video)
    {
        $this->authorize('delete', $video);

        Storage::disk('public')->delete($video->video_path);
        $video->delete();

        return redirect()->route('prestataire.videos.manage')->with('success', 'Vidéo supprimée avec succès.');
    }
}