<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectCommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskProgressImageController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\InquiryController;
use App\Models\Inquiry;
use App\Models\Project;
use App\Models\ProjectMessage;
use App\Models\Task;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();
    $projectIds = $user->projects()->pluck('projects.id');
    $statusLabels = [
        'todo' => 'To do',
        'doing' => 'Doing',
        'done' => 'Done',
    ];

    $projectActivities = Project::query()
        ->whereIn('id', $projectIds)
        ->latest('updated_at')
        ->take(10)
        ->get()
        ->map(fn (Project $project) => [
            'id' => "project-{$project->id}",
            'module' => 'projects',
            'title' => 'Project updated',
            'description' => $project->name,
            'href' => route('projects.show', $project),
            'time' => optional($project->updated_at)->toISOString(),
            'sort_at' => optional($project->updated_at)->timestamp ?? 0,
        ]);

    $taskActivities = Task::query()
        ->whereIn('project_id', $projectIds)
        ->with('project:id,name')
        ->latest('updated_at')
        ->take(10)
        ->get()
        ->map(function (Task $task) use ($statusLabels) {
            $status = $statusLabels[$task->status] ?? Str::headline((string) $task->status);
            $projectName = optional($task->project)->name ?? 'Unknown project';

            return [
                'id' => "task-{$task->id}",
                'module' => 'projects',
                'title' => 'Task changed',
                'description' => "{$task->title} • {$projectName} • {$status}",
                'href' => route('projects.show', $task->project_id),
                'time' => optional($task->updated_at)->toISOString(),
                'sort_at' => optional($task->updated_at)->timestamp ?? 0,
            ];
        });

    $chatActivities = ProjectMessage::query()
        ->whereIn('project_id', $projectIds)
        ->with(['project:id,name', 'user:id,name'])
        ->latest('created_at')
        ->take(10)
        ->get()
        ->map(function (ProjectMessage $message) {
            $author = optional($message->user)->name ?? 'Unknown user';
            $projectName = optional($message->project)->name ?? 'Unknown project';
            $body = Str::limit(trim((string) $message->body), 90);

            return [
                'id' => "chat-{$message->id}",
                'module' => 'chat',
                'title' => "{$author} posted a message",
                'description' => $body !== '' ? "{$projectName} • {$body}" : "{$projectName} • Attachment message",
                'href' => route('chat.show', $message->project_id),
                'time' => optional($message->created_at)->toISOString(),
                'sort_at' => optional($message->created_at)->timestamp ?? 0,
            ];
        });

    $inquiryActivities = Inquiry::query()
        ->latest('updated_at')
        ->take(10)
        ->get()
        ->map(function (Inquiry $inquiry) {
            $status = Str::headline((string) $inquiry->status);

            return [
                'id' => "inquiry-{$inquiry->id}",
                'module' => 'inquiries',
                'title' => "Inquiry {$status}",
                'description' => "{$inquiry->name} • {$inquiry->email}",
                'href' => route('inquiries.show', $inquiry),
                'time' => optional($inquiry->updated_at)->toISOString(),
                'sort_at' => optional($inquiry->updated_at)->timestamp ?? 0,
            ];
        });

    $activities = collect()
        ->merge($projectActivities)
        ->merge($taskActivities)
        ->merge($chatActivities)
        ->merge($inquiryActivities)
        ->sortByDesc('sort_at')
        ->take(20)
        ->map(function (array $activity) {
            unset($activity['sort_at']);

            return $activity;
        })
        ->values();

    $cutoff = now()->subDay();
    $recentCounts = [
        'projects' => Project::whereIn('id', $projectIds)->where('updated_at', '>=', $cutoff)->count(),
        'chat' => ProjectMessage::whereIn('project_id', $projectIds)->where('created_at', '>=', $cutoff)->count(),
        'inquiries' => Inquiry::where('updated_at', '>=', $cutoff)->count(),
    ];

    return Inertia::render('Dashboard', [
        'activities' => $activities,
        'recentCounts' => $recentCounts,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/', [PublicPageController::class, 'home'])->name('public.home');
Route::get('/services', [PublicPageController::class, 'services'])->name('public.services');
Route::get('/portfolio', [PublicPageController::class, 'portfolio'])->name('public.portfolio');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('public.contact');

Route::post('/contact', [InquiryController::class, 'store'])->name('public.contact.store');

Route::match(['get', 'post'], '/locale/{locale?}', function (Request $request, ?string $locale = null) {
    $locale = $locale ?? $request->input('locale', 'et');

    if (!in_array($locale, ['et', 'en', 'fi'], true)) {
        $locale = 'et';
    }

    session(['locale' => $locale]);
    app()->setLocale($locale);

    $redirect = $request->input('redirect');

    if (!is_string($redirect) || $redirect === '') {
        $previous = url()->previous();
        $path = parse_url($previous, PHP_URL_PATH) ?: '/';
        $query = parse_url($previous, PHP_URL_QUERY);

        $redirect = $query ? "{$path}?{$query}" : $path;
    }

    if (!str_starts_with($redirect, '/')) {
        $redirect = '/';
    }

    return redirect()->to($redirect, 303);
})->name('locale.set');

Route::middleware(['auth'])->group(function () {
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::post('/projects/{project}/tasks', [ProjectController::class, 'storeTask'])->name('projects.tasks.store');
    Route::patch('/tasks/{task}', [ProjectController::class, 'updateTask'])->name('tasks.update');
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])
        ->name('tasks.comments.store');
    Route::delete('/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])
        ->name('tasks.comments.destroy');
    Route::post('/tasks/{task}/progress-images', [TaskProgressImageController::class, 'store'])
        ->name('tasks.progress-images.store');
    Route::delete('/tasks/{task}/progress-images/{image}', [TaskProgressImageController::class, 'destroy'])
        ->name('tasks.progress-images.destroy');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{project}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{project}/messages', [ChatController::class, 'store'])->name('chat.messages.store');
    Route::delete('/chat/{project}/messages/{message}', [ChatController::class, 'destroy'])->name('chat.messages.destroy');
    Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::get('/inquiries/{inquiry}', [InquiryController::class, 'show'])->name('inquiries.show');
    Route::patch('/inquiries/{inquiry}/status', [InquiryController::class, 'updateStatus'])->name('inquiries.status');
    Route::post('/inquiries/{inquiry}/convert', [InquiryController::class, 'convert'])->name('inquiries.convert');
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember'])
        ->name('projects.members.store');
    Route::delete('/projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])
        ->name('projects.members.destroy');
    Route::patch('/projects/{project}/members/{user}', [ProjectMemberController::class, 'update'])
        ->name('projects.members.update');
    Route::post('/projects/{project}/comments', [ProjectController::class, 'storeComment'])
        ->name('projects.comments.store');
    Route::delete('/projects/{project}/comments/{comment}', [ProjectCommentController::class, 'destroy'])
        ->name('projects.comments.destroy');
    Route::delete('/tasks/{task}', [ProjectController::class, 'destroyTask'])
        ->name('tasks.destroy');
});


        
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



 

Route::get('/chat/{project}/messages', [ChatController::class, 'messages'])
    ->name('chat.messages.index');

Route::get('/chat/{project}/attachments/{attachment}/view', [ChatController::class, 'viewAttachment'])
    ->name('chat.attachments.view');

Route::get('/chat/{project}/attachments/{attachment}', [ChatController::class, 'download'])
    ->name('chat.attachments.download');


require __DIR__.'/auth.php';
