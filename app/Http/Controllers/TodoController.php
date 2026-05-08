<?php

namespace App\Http\Controllers;

use App\Models\TodoTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TodoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $this->nullableSquish($request->query('search'));

        $tasks = TodoTask::query()
            ->with('user:id,name,email')
            ->where('user_id', $request->user()->id)
            ->when($search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("case status when 'assigned' then 1 when 'in_progress' then 2 when 'upcoming' then 3 when 'completed' then 4 else 5 end")
            ->orderByRaw('case when due_date is null then 1 else 0 end')
            ->orderBy('due_date')
            ->orderByDesc('updated_at')
            ->get();

        $statusOptions = TodoTask::statusOptions();
        $groupedTasks = collect($statusOptions)
            ->map(fn (string $label, string $status) => [
                'status' => $status,
                'label' => $label,
                'tasks' => $tasks->where('status', $status)->values(),
            ])
            ->values();

        return view('todo', [
            'tasks' => $tasks,
            'groupedTasks' => $groupedTasks,
            'statusOptions' => $statusOptions,
            'priorityOptions' => TodoTask::priorityOptions(),
            'search' => $search,
            'summary' => [
                'total' => $tasks->count(),
                'open' => $tasks->where('status', '!=', TodoTask::STATUS_COMPLETED)->count(),
                'completed' => $tasks->where('status', TodoTask::STATUS_COMPLETED)->count(),
                'due_today' => $tasks->filter(fn (TodoTask $task) => $task->due_date?->isToday())->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TodoTask::query()->create(array_merge($this->validatedData($request), [
            'user_id' => $request->user()->id,
        ]));

        return redirect()
            ->route('todo.index')
            ->with('toast-success', 'Task created successfully.');
    }

    public function update(Request $request, TodoTask $todoTask): RedirectResponse
    {
        $this->authorizeTask($request, $todoTask);

        $todoTask->update($this->validatedData($request));

        return redirect()
            ->route('todo.index')
            ->with('toast-success', 'Task updated successfully.');
    }

    public function toggle(Request $request, TodoTask $todoTask): RedirectResponse
    {
        $this->authorizeTask($request, $todoTask);

        $todoTask->update([
            'status' => $todoTask->isCompleted() ? TodoTask::STATUS_ASSIGNED : TodoTask::STATUS_COMPLETED,
            'completed_at' => $todoTask->isCompleted() ? null : now(),
        ]);

        return back()->with('toast-success', 'Task status updated.');
    }

    public function destroy(Request $request, TodoTask $todoTask): RedirectResponse
    {
        $this->authorizeTask($request, $todoTask);

        $todoTask->delete();

        return redirect()
            ->route('todo.index')
            ->with('toast-success', 'Task deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(array_keys(TodoTask::statusOptions()))],
            'priority' => ['required', Rule::in(array_keys(TodoTask::priorityOptions()))],
            'due_date' => ['nullable', 'date'],
        ]);

        $status = $validated['status'];

        return [
            'title' => $this->squish($validated['title']),
            'description' => $this->nullableTrim($validated['description'] ?? null),
            'status' => $status,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'completed_at' => $status === TodoTask::STATUS_COMPLETED ? Carbon::now() : null,
        ];
    }

    private function authorizeTask(Request $request, TodoTask $todoTask): void
    {
        abort_unless($todoTask->user_id === $request->user()->id, 404);
    }

    private function squish(string $value): string
    {
        return (string) Str::of($value)->squish();
    }

    private function nullableSquish(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return $this->squish($value);
    }

    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
