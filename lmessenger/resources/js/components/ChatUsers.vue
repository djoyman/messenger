<template>
	<div class="users-container" id="users">
		<h3>Online</h3>
		<hr>
		<ul>
			<li v-for="user in users" :key="user.id">{{ user.name }}</li>
		</ul>
	</div>
</template>

<script>
export default {
	props: {
		users: {
			type: Array,
			required: true,
		},
	},
}
</script>

<style lang="scss" scoped>

	h3 {
		font-size: 20px;
		color: #fff;
		padding: 20px 0px 20px 20px;
		margin: 0;
	}

	hr {
		margin: 1px 8px;
	}

	li {
		color: #fff;
		padding: 14px;
	}

	.users-container {
		height: 100%;
		width: 0;
		position: fixed;
		z-index: 3;
		top: 0;
		right: 0;
		background: rgb(25, 37, 51);
		overflow-x: hidden;
		overflow-y: scroll;
		transition: width 0.5s ease-out;
	}
</style>