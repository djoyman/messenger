<template>
	<div class="login-container">
		<a class="login" :href="`${route}/vkontakte`">
			<img class="img" src="/images/logos/vk.png">
			<h2 class="text">Вход ВКонтакте</h2>
		</a>
	</div>
</template>

<script>
export default {
	props: {
		route: {
			type: String,
			required: true
		}
	}
}
</script>

<style lang="scss" scoped>
	.login-container {
		height: 100vh;
		width: 100%;
		background: rgb(25, 37, 51);
		color: #eee;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		text-align: center;

		.login {
			width: 220px;
			display: flex;
			align-items: center;
			justify-content: center;
			border: 1px solid rgb(50, 62, 79);
			border-radius: 5px;
			box-shadow: 0 0 16px rgba($color: #000000, $alpha: 0.20);
			padding: 12px;
			text-decoration: none;
			color: #eee;

			.img {
				width: 48px;
				height: 48px;
				margin-right: 8px;
			}

			.text {
				font-size: 14px;
				margin: 0;
				margin-left: 8px;
			}
		}
	}
</style>