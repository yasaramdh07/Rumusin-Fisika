@props(['size' => 'md'])
<span {{ $attributes->class(['rf-ruka-avatar', 'rf-ruka-avatar--'.$size => in_array($size, ['sm', 'lg'], true)]) }} aria-hidden="true">R</span>