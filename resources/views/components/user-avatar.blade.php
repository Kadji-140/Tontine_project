@props(['user', 'size' => 40, 'circle' => true, 'class' => ''])

@php
    // Initials logic: First letter of first two words, or first 2 letters
    $parts = explode(' ', $user->name);
    if(count($parts) >= 2) {
        $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    } else {
        $initials = strtoupper(substr($user->name, 0, 2));
    }

    $shapeClass = $circle ? 'rounded-circle' : 'rounded-3';
    $fontSize = max(10, $size * 0.4); // Dynamic font size based on dimensions
@endphp

@if($user->avatar)
    <img src="{{ asset('storage/' . $user->avatar) }}" 
         alt="{{ $user->name }}" 
         class="{{ $shapeClass }} object-fit-cover shadow-sm border border-light {{ $class }}"
         style="width: {{ $size }}px; height: {{ $size }}px;">
@else
    <div class="{{ $shapeClass }} d-inline-flex align-items-center justify-content-center fw-bold shadow-sm border border-light text-white {{ $class }}"
         style="width: {{ $size }}px; 
                height: {{ $size }}px; 
                font-size: {{ $fontSize }}px; 
                background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
                user-select: none;">
        {{ $initials }}
    </div>
@endif
