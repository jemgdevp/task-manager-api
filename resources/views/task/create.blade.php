<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Task Created</title>
</head>

<body style="font-family: Arial, sans-serif; max-width: 640px; margin: 0 auto; padding: 16px; color: #1f2937;">
	<h1 style="margin-bottom: 8px;">✅ New task created</h1>
	<p style="margin-top: 0;">Hi {{ $user?->name ?? 'there' }}, your task has been created successfully.</p>

	<div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; background: #f9fafb;">
		<p><strong>Title:</strong> {{ $task->title }}</p>
		<p><strong>Description:</strong> {{ $task->description ?: 'No description provided' }}</p>
		<p><strong>Status:</strong> {{ $task->status }}</p>
		<p><strong>Due date:</strong> {{ $task->due_date?->format('Y-m-d H:i') ?? 'Not set' }}</p>
	</div>

	<p style="margin-top: 16px; color: #6b7280; font-size: 12px;">
		This is an automated email from Task Manager API.
	</p>
</body>

</html>
