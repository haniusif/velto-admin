@props(['ar' => '', 'en' => '', 'block' => false, 'tag' => 'span'])
@php
  // Both languages are in the markup; CSS shows the one matching html[data-lang].
  // A missing translation falls back to the other so nothing renders empty.
  $arText = $ar !== '' && $ar !== null ? $ar : $en;
  $enText = $en !== '' && $en !== null ? $en : $ar;
@endphp
<{{ $tag }} {{ $attributes->merge(['data-i18n' => $block ? 'block' : true]) }}><span data-ar>{{ $arText }}</span><span data-en>{{ $enText }}</span></{{ $tag }}>
