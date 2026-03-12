<!-- TEST Maya is working -->
<ul class="task-list">
    @foreach ($tasks as $task)
        <li class="task-item">{{ $task->name }}</li>
    @endforeach
</ul>