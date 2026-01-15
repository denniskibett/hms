<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreTaskRequest;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskType;
use App\Services\OperationsService;
use App\Services\CoreService;

class TaskController extends Controller
{
    public function __construct(
        private OperationsService $operationsService,
        private CoreService $coreService
    ) {}
    
    public function index(Request $request)
    {
        // $this->authorize('viewAny', Task::class);
        
        $tasks = $this->operationsService->getTasks($request->all());
        $taskTypes = TaskType::all();
        $departments = \App\Models\Department::all();
        $users = User::all();
        $stats = $this->operationsService->getTaskStats();
        
        return view('tasks.index', compact('tasks', 'taskTypes', 'departments', 'users', 'stats'));
    }
    
    public function create()
    {
        $this->authorize('create', Task::class);
        
        $taskTypes = TaskType::all();
        $departments = \App\Models\Department::all();
        $rooms = \App\Models\Room::all();
        $staff = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'guest');
        })->get();
        
        return view('tasks.create', compact('taskTypes', 'departments', 'rooms', 'staff'));
    }
    
    public function store(StoreTaskRequest $request)
    {
        $this->authorize('create', Task::class);
        
        try {
            $task = $this->operationsService->createTask($request->validated());
            
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Task created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating task: ' . $e->getMessage());
        }
    }
    

    public function show(Task $task)
    {
        // $this->authorize('view', $task);
        
        $task->load([
            'taskType',
            'assignedStaff',
            'room',
            'stay.guest',
            'department',
            'items.inventoryItem',
            'comments.user',
        ]);
        
        return view('tasks.show', compact('task'));
    }
    
    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        
        $taskTypes = TaskType::all();
        $departments = \App\Models\Department::all();
        $rooms = \App\Models\Room::all();
        $staff = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'guest');
        })->get();
        
        return view('tasks.edit', compact('task', 'taskTypes', 'departments', 'rooms', 'staff'));
    }
    
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_type_id' => 'required|exists:task_types,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'required|date',
            'estimated_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);
        
        try {
            $task->update($validated);
            
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Task updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating task: ' . $e->getMessage());
        }
    }
    
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        
        try {
            $task->delete();
            
            return redirect()->route('tasks.index')
                ->with('success', 'Task deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting task: ' . $e->getMessage());
        }
    }
    
    public function assign(Task $task, Request $request)
    {
        $this->authorize('assign', $task);
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        
        try {
            $task = $this->operationsService->assignTask($task, $request->input('user_id'));
            
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Task assigned successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error assigning task: ' . $e->getMessage());
        }
    }
    

    public function start(Task $task, Request $request)
    {
        $this->authorize('update', $task);
        
        try {
            $task = $this->operationsService->startTask($task, $request->input('notes'));
            
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Task started successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error starting task: ' . $e->getMessage());
        }
    }
    
    public function complete(Task $task, Request $request)
    {
        $this->authorize('complete', $task);
        
        $request->validate([
            'actual_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);
        
        try {
            $task = $this->operationsService->completeTask($task, $request->all());
            
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Task completed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error completing task: ' . $e->getMessage());
        }
    }

    public function verify(Task $task, Request $request)
    {
        $this->authorize('verify', $task);
        
        $request->validate([
            'notes' => 'nullable|string',
        ]);
        
        try {
            $task = $this->operationsService->verifyTask($task, $request->user(), $request->input('notes'));
            
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Task verified successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error verifying task: ' . $e->getMessage());
        }
    }

    public function useInventory(Task $task, Request $request)
    {
        $this->authorize('update', $task);
        
        $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);
        
        try {
            $taskItem = $this->operationsService->useInventoryInTask(
                $task,
                $request->input('inventory_item_id'),
                $request->input('quantity'),
                $request->input('unit_cost')
            );
            
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Inventory item used successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error using inventory item: ' . $e->getMessage());
        }
    }
    
    /**
     * Add comment to task.
     */
    public function addComment(Task $task, Request $request)
    {
        $this->authorize('comment', $task);
        
        $request->validate([
            'content' => 'required|string',
        ]);
        
        try {
            $comment = $task->addComment($request->input('content'));
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'comment' => $comment,
                ]);
            }
            
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Comment added successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error adding comment',
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error adding comment: ' . $e->getMessage());
        }
    }
    
    public function updateChecklist(Task $task, Request $request)
    {
        $this->authorize('update', $task);
        
        $request->validate([
            'item_index' => 'required|integer|min:0',
            'completed' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);
        
        try {
            $task = $this->operationsService->updateChecklistItem(
                $task,
                $request->input('item_index'),
                $request->input('completed'),
                $request->input('notes')
            );
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'task' => $task,
                ]);
            }
            
            return redirect()->route('tasks.show', $task)
                ->with('success', 'Checklist updated successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating checklist',
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Error updating checklist: ' . $e->getMessage());
        }
    }

    public function myTasks(Request $request)
    {
        $user = $request->user();
        
        $tasks = $this->operationsService->getTasks([
            'assigned_to' => $user->id,
            'status' => $request->input('status', ['pending', 'assigned', 'in_progress']),
            'per_page' => 20,
        ]);
        
        return view('tasks.my-tasks', compact('tasks', 'user'));
    }
    
    public function overdue()
    {
        $this->authorize('viewAny', Task::class);
        
        $tasks = Task::with(['taskType', 'assignedStaff', 'room'])
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'verified', 'cancelled'])
            ->orderBy('due_date')
            ->paginate(20);
        
        return view('tasks.overdue', compact('tasks'));
    }
}