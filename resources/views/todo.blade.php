@extends('layouts.vertical', ['title' => 'Todo'])

@section('content')
<div class="row mb-3">
    <div class="col-lg-8">
        <h4 class="fw-semibold mb-1">Todo</h4>
        <p class="text-muted mb-0">{{ number_format($summary['open']) }} open task{{ $summary['open'] === 1 ? '' : 's' }} from the database.</p>
    </div>
    <div class="col-lg-4 mt-3 mt-lg-0">
        <form action="{{ route('todo.index') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text"><i data-lucide="search" class="icon-sm"></i></span>
                <input class="form-control" name="search" placeholder="Search tasks" type="search" value="{{ $search }}">
                @if($search)
                    <a class="btn btn-outline-secondary" href="{{ route('todo.index') }}">Clear</a>
                @endif
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <p class="fw-semibold mb-1">Check the task details and try again.</p>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-xl-8">
        @if($tasks->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5">
                    <span class="avatar-md rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mb-2">
                        <i data-lucide="list-checks"></i>
                    </span>
                    <p class="text-muted mb-0">No tasks found in the database.</p>
                </div>
            </div>
        @else
        @foreach($groupedTasks as $section)
            @continue($section['tasks']->isEmpty())
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between gap-3">
                    <h5 class="card-title mb-0">{{ $section['label'] }}</h5>
                    <span class="badge bg-light text-dark">{{ $section['tasks']->count() }}</span>
                </div>
                <div class="card-body">
                    @foreach($section['tasks'] as $task)
                        @php
                            $isCompleted = $task->isCompleted();
                            $isOverdue = $task->due_date && $task->due_date->lt(today()) && ! $isCompleted;
                        @endphp
                        <div class="mb-2 border rounded">
                            <div class="p-2">
                                <div class="row align-items-center justify-content-between g-3">
                                    <div class="col-12 col-md-7">
                                        <div class="d-flex align-items-start justify-content-start gap-2">
                                            <form action="{{ route('todo.toggle', $task) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input class="form-check-input rounded-circle mt-1 fs-16" type="checkbox" @checked($isCompleted) onchange="this.form.submit()" aria-label="Toggle {{ $task->title }}">
                                            </form>
                                            <div>
                                                <p class="mb-1 fw-medium {{ $isCompleted ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                                    <span class="text-primary fw-semibold">{{ $task->title }}</span>
                                                </p>
                                                @if($task->description)
                                                    <p class="text-muted mb-0">{{ $task->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <div class="d-flex align-items-center gap-2 justify-content-md-end flex-wrap">
                                            <span class="badge {{ $task->statusBadgeClass() }}">{{ $task->statusLabel() }}</span>
                                            <span class="fw-semibold fs-13 {{ $isOverdue ? 'text-danger' : 'text-muted' }}">
                                                {{ $task->due_date ? $task->due_date->format('d M Y') : 'No due date' }}
                                            </span>
                                            <span class="badge {{ $task->priorityBadgeClass() }} p-1">{{ $task->priorityLabel() }}</span>
                                            <div class="dropdown">
                                                <a aria-expanded="false" class="ps-1" data-bs-toggle="dropdown" href="javascript: void(0);">
                                                    <i class="align-middle fs-16" data-lucide="ellipsis-vertical"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" data-bs-toggle="collapse" href="#editTask{{ $task->id }}" role="button" aria-expanded="false" aria-controls="editTask{{ $task->id }}">
                                                        <i class="align-middle me-2" data-lucide="square-pen"></i>Edit
                                                    </a>
                                                    <form action="{{ route('todo.destroy', $task) }}" method="POST" data-delete-confirm data-delete-title="Delete task?" data-delete-message="Do you want to delete this task?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="dropdown-item text-danger" type="submit">
                                                            <i class="align-middle me-2" data-lucide="trash-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="collapse mt-3" id="editTask{{ $task->id }}">
                                    <form action="{{ route('todo.update', $task) }}" method="POST" class="border-top pt-3">
                                        @csrf
                                        @method('PATCH')
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label class="form-label" for="title{{ $task->id }}">Task</label>
                                                <input class="form-control" id="title{{ $task->id }}" name="title" required type="text" value="{{ $task->title }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="status{{ $task->id }}">Status</label>
                                                <select class="form-select" id="status{{ $task->id }}" name="status" required>
                                                    @foreach($statusOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected($task->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="priority{{ $task->id }}">Priority</label>
                                                <select class="form-select" id="priority{{ $task->id }}" name="priority" required>
                                                    @foreach($priorityOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected($task->priority === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="dueDate{{ $task->id }}">Due Date</label>
                                                <input class="form-control" id="dueDate{{ $task->id }}" name="due_date" type="date" value="{{ $task->due_date?->format('Y-m-d') }}">
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label" for="description{{ $task->id }}">Description</label>
                                                <input class="form-control" id="description{{ $task->id }}" name="description" type="text" value="{{ $task->description }}">
                                            </div>
                                            <div class="col-12 text-end">
                                                <button class="btn btn-primary btn-sm" type="submit">
                                                    <i class="icon-sm me-1" data-lucide="save"></i>Save Task
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        @endif
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Add Task</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('todo.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="newTaskTitle">Task</label>
                        <input class="form-control" id="newTaskTitle" name="title" required type="text" value="{{ old('title') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="newTaskDescription">Description</label>
                        <textarea class="form-control" id="newTaskDescription" name="description" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label" for="newTaskStatus">Status</label>
                            <select class="form-select" id="newTaskStatus" name="status" required>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', 'assigned') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="newTaskPriority">Priority</label>
                            <select class="form-select" id="newTaskPriority" name="priority" required>
                                @foreach($priorityOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label" for="newTaskDueDate">Due Date</label>
                        <input class="form-control" id="newTaskDueDate" name="due_date" type="date" value="{{ old('due_date') }}">
                    </div>
                    <div class="d-grid mt-3">
                        <button class="btn btn-primary" type="submit">
                            <i class="icon-sm me-1" data-lucide="plus"></i>Add Task
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Database Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2">
                    <span class="text-muted">Total</span>
                    <span class="fw-semibold">{{ number_format($summary['total']) }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2">
                    <span class="text-muted">Open</span>
                    <span class="fw-semibold">{{ number_format($summary['open']) }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2">
                    <span class="text-muted">Completed</span>
                    <span class="fw-semibold">{{ number_format($summary['completed']) }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted">Due Today</span>
                    <span class="fw-semibold">{{ number_format($summary['due_today']) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
