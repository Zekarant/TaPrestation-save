<!DOCTYPE html>
<html>
<head>
    <title>Test Video Playback</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>Video Playback Test</h1>
    
    <h2>Direct Video Test</h2>
    <video width="600" height="400" controls>
        <source src="{{ App\Models\Video::first()->video_url }}" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    
    <h2>Video URL:</h2>
    <p>{{ App\Models\Video::first()->video_url }}</p>
    
    <h2>Video Info:</h2>
    <pre>{{ print_r(App\Models\Video::first()->toArray(), true) }}</pre>
    
    <h2>File Exists Check:</h2>
    <p>File exists: {{ file_exists(storage_path('app/public/' . App\Models\Video::first()->video_path)) ? 'Yes' : 'No' }}</p>
</body>
</html>