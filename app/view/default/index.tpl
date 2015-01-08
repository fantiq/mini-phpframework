<{php=time()}>
hello <{user}>
<ul>
	<{foreach data->d}>
		<li><{d.user}></li>
	<{/foreach}>
</ul>

<{foreach data=key->val}>
<{/foreach}>