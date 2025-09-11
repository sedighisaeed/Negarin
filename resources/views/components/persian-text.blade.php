@props([
    'tag' => 'div',
    'weight' => 'regular',
    'size' => null,
    'rtl' => true
])

@php
$classes = [
    'persian-text',
    'vazir-' . $weight,
    $rtl ? 'text-right' : '',
    $attributes->get('class', '')
];

$style = '';
if ($size) {
    $style .= "font-size: {$size};";
}
if ($attributes->has('style')) {
    $style .= ' ' . $attributes->get('style');
}
@endphp

<{{ $tag }} 
    {{ $attributes->merge([
        'class' => implode(' ', array_filter($classes)),
        'dir' => $rtl ? 'rtl' : 'ltr',
        'lang' => 'fa'
    ]) }}
    @if($style) style="{{ $style }}" @endif
>
    {{ $slot }}
</{{ $tag }}>