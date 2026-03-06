<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Inquiry;
use Inertia\Inertia;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $data['status'] = 'new';
        Inquiry::create($data);

        return back()->with('success', 'Inquiry received successfully.');
    }


    public function index(Request $request)
    {
    $status = $request->query('status', 'new');

    $allowed = ['new', 'contacted', 'converted'];
    if (!in_array($status, $allowed, true)) {
        $status = 'new';
    }

    $inquiries = Inquiry::query()
        ->where('status', $status)
        ->latest()
        ->paginate(20)
        ->withQueryString()
        ->through(fn ($i) => [
            'id' => $i->id,
            'name' => $i->name,
            'email' => $i->email,
            'phone' => $i->phone,
            'status' => $i->status,
            'message_preview' => mb_strimwidth($i->message, 0, 120, '…'),
            'created_at' => $i->created_at->toDateTimeString(),
        ]);

    $counts = [
        'new' => Inquiry::where('status', 'new')->count(),
        'contacted' => Inquiry::where('status', 'contacted')->count(),
        'converted' => Inquiry::where('status', 'converted')->count(),
    ];

    return Inertia::render('Inquiries/Index', [
        'inquiries' => $inquiries,
        'filters' => [
            'status' => $status,
        ],
        'counts' => $counts,
    ]);
    }


    public function show(Inquiry $inquiry)
    {
        return Inertia::render('Inquiries/Show', [
            'inquiry' => [
                'id' => $inquiry->id,
                'name' => $inquiry->name,
                'email' => $inquiry->email,
                'phone' => $inquiry->phone,
                'message' => $inquiry->message,
                'created_at' => $inquiry->created_at->toDateTimeString(),
                'status' => $inquiry->status,
                'project_id' => $inquiry->project?->id,
            ]
        ]);
    }


    public function updateStatus(Request $request, Inquiry $inquiry)
    {
    $data = $request->validate([
        'status' => ['required', 'in:new,contacted,converted'],
    ]);

    $inquiry->update(['status' => $data['status']]);

    $message = match ($data['status']) {
        'new' => 'Inquiry moved to new.',
        'contacted' => 'Inquiry marked as contacted.',
        'converted' => 'Inquiry marked as converted.',
        default => 'Inquiry status updated.',
    };

    return back()->with('success', $message);
    }


    public function convert(Request $request, Inquiry $inquiry)
    {
    $user = $request->user();

    if ($inquiry->status === 'converted' && $inquiry->project) {
        return redirect()->route('projects.show', $inquiry->project->id);
    }

    $project = \App\Models\Project::create([
        'name' => 'Inquiry #' . $inquiry->id . ' - ' . $inquiry->name,
        'status' => 'new',
        'inquiry_id' => $inquiry->id,
    ]);

    $project->users()->syncWithoutDetaching([
    $user->id => ['role' => 'office'],
    ]);

    $project->users()->updateExistingPivot($user->id, ['role' => 'office']);

    $inquiry->update(['status' => 'converted']);

    return redirect()->route('projects.show', $project->id);
    }

}
