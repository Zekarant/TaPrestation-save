<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use getID3;

class StoreVideoFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'video' => [
                'required',
                'file',
                'mimes:mp4,mov,webm,avi,wmv,mpeg,mpg,3gp,3g2,flv,m4v',
                'max:512000', // 500MB
                function ($attribute, $value, $fail) {
                    // Check video duration using getID3
                    try {
                        // Ensure we have a valid file object
                        if (!is_object($value) || !method_exists($value, 'getPathname')) {
                            \Log::warning('Invalid file object received for video validation');
                            return; // Skip validation if not a proper file object
                        }

                        $getID3 = new getID3();

                        // Enable certain modules for better format support
                        $getID3->option_md5_data = true;
                        $getID3->option_md5_data_source = true;
                        $getID3->encoding = 'UTF-8';

                        $fileInfo = $getID3->analyze($value->getPathname());

                        // Try to get duration from different possible sources
                        $duration = null;

                        if (isset($fileInfo['playtime_seconds']) && is_numeric($fileInfo['playtime_seconds'])) {
                            $duration = $fileInfo['playtime_seconds'];
                        } elseif (isset($fileInfo['video']['playtime_seconds']) && is_numeric($fileInfo['video']['playtime_seconds'])) {
                            $duration = $fileInfo['video']['playtime_seconds'];
                        } elseif (isset($fileInfo['audio']['playtime_seconds']) && is_numeric($fileInfo['audio']['playtime_seconds'])) {
                            $duration = $fileInfo['audio']['playtime_seconds'];
                        }

                        // If we still don't have duration, try to calculate from other data
                        if ($duration === null && isset($fileInfo['bitrate']) && isset($fileInfo['filesize'])) {
                            // Rough calculation: duration = filesize / bitrate
                            // This is not very accurate but better than rejecting the file
                            if ($fileInfo['bitrate'] > 0) {
                                $duration = $fileInfo['filesize'] / ($fileInfo['bitrate'] / 8);
                            }
                        }

                        if ($duration !== null) {
                            if ($duration > 60) {
                                $fail('La vidéo ne doit pas dépasser 60 secondes. Durée détectée: ' . round($duration, 2) . ' secondes.');
                            }
                        } else {
                            // Instead of failing, we'll allow the file to be uploaded and let the processing job handle it
                            // This prevents issues with files that getID3 might have trouble analyzing immediately
                            \Log::warning('Could not determine video duration during validation', [
                                'filename' => $value->getClientOriginalName(),
                                'filesize' => $value->getSize(),
                                'mime_type' => $value->getMimeType(),
                                'file_info' => $fileInfo
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Log the error but don't fail the validation
                        // Let the processing job handle the file analysis
                        \Log::error('Error analyzing video during validation: ' . $e->getMessage(), [
                            'filename' => $value->getClientOriginalName(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                },
            ],
        ];
    }
}