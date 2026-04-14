@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
{!! $slot !!}<span class="dot">·</span>
</a>
</td>
</tr>
