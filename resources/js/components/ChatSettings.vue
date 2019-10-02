<template>
	<div class="settings-container" id="settings">
		<h3>Settings</h3>
		<span>{{ user.id }} {{ user.name }}</span>
	</div>
</template>

<script>
export default {
	props: {
		user: {
			type: Object,
			required: true
		}
	}
}
</script>

<style lang="scss" scoped>
	h3 {
		font-size: 20px;
		color: #fff;
		padding: 20px 0px 20px 20px;
		margin: 0;
	}

	span {
		font-size: 16px;
		color: #fff;
		padding: 20px 0px 20px 20px;
		margin: 0;
	}
	
	.settings-container {
		height: 100%;
		width: 0;
		position: fixed;
		z-index: 3;
		top: 0;
		left: 0;
		background: rgb(25, 37, 51);
		overflow-x: hidden;
		overflow-y: scroll;
		transition: width 0.5s ease-out;
	}
</style>