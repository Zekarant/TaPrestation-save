@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Send Simple Notification</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('notifications.simple.send') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="user_id">Select User</label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">Choose a user</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="message">Message</label>
                            <textarea name="message" id="message" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label for="action_url">Action URL (optional)</label>
                            <input type="url" name="action_url" id="action_url" class="form-control">
                        </div>

                        <div class="form-group mb-3">
                            <label for="action_text">Action Text (optional)</label>
                            <input type="text" name="action_text" id="action_text" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Send Notification</button>
                    </form>

                    <hr>

                    <h4>Send to All Users</h4>
                    <form method="POST" action="{{ route('notifications.simple.send-all') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="bulk_message">Message</label>
                            <textarea name="message" id="bulk_message" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label for="bulk_action_url">Action URL (optional)</label>
                            <input type="url" name="action_url" id="bulk_action_url" class="form-control">
                        </div>

                        <div class="form-group mb-3">
                            <label for="bulk_action_text">Action Text (optional)</label>
                            <input type="text" name="action_text" id="bulk_action_text" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-warning">Send to All Users</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection