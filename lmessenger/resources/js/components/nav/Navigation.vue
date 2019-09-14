<template>
	<div class="nav-container">
		<!-- <button class="menu" @click.prevent="registerSettingsClick" >
			<i class="fas fa-cog fa-lg"></i>
		</button> -->
		<h2 class="header" v-if="typing.length === 0">
			{{ room.title }}
		</h2>
		<h2 :class="`header ${ typing.length > 1 ? 'multiple-users' : 'single-user' }`" v-else-if="typing.length > 0 && typing.length <= 2">
			{{ typing.map(e => e.name).join(', ') }} 
			<div class="animation-container">
				<span class="item">.</span>
				<span class="item">.</span>
				<span class="item">.</span>
			</div>
		</h2>
		<h2 class="header multiple-users" v-else>
			Печатают много 
			<div class="animation-container">
				<span class="item">.</span>
				<span class="item">.</span>
				<span class="item">.</span>
			</div>
		</h2>
		<button class="user-list" @click.prevent="registerUserListClick" >
			<i class="fas fa-users fa-lg"></i>
		</button>
	</div>
</template>

<script>
export default {
	props: {
		user: {
			type: Object,
			required: true
		},
		room: {
			type: Object,
			required: true
		},
		typing: {
			type: Array,
			required: true
		}
	},

	methods: {

		registerUserListClick() {
			this.$emit('userListClickEvent');
		},

		// registerSettingsClick() {
		// 	this.$emit('settingsClickEvent');
		// }
	}
}
</script>

<style lang="scss" scoped>
	.nav-container {
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		height: 60px;
		background: rgb(25, 37, 51);
		box-shadow: 0 2px 2px rgba($color: #000000, $alpha: 0.1);
		z-index: 1;
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 0 8px;

		.menu, .user-list {
			width: 40px;
			background: none;
			color: #eee;
			border: none;
			padding: 4px;
			margin: 4px 0;
			outline: none;
		}

		.header {
			color: #eee;
			font-size: 20px;
			text-align: center;
			margin: 0;
			margin-left: 10px;
			display: flex;
			max-width: 85%;
			text-overflow: ellipsis;

			&.single-user {
				font-size: 16px;
			}

			&.multiple-users {
				font-size: 14px;
			}

			.animation-container {
				margin-left: 4px;

				.item {
					-webkit-animation: opacity 1.4s steps(60, start) 0s infinite;
					animation: opacity 1.4s steps(60, start) 0s infinite;
				}

				.item:nth-child(1) {
					-webkit-animation-delay: -1.4s;
					animation-delay: -1.4s;
				}
				
				.item:nth-child(2) {
					-webkit-animation-delay: -1.2s;
					animation-delay: -1.2s;
				}
				
				.item:nth-child(3) {
					-webkit-animation-delay: -1s;
					animation-delay: -1s;
				}

				@-webkit-keyframes opacity {
					0% {opacity:1;}
					100% {opacity:0;}
				}

				@-o-keyframes opacity {
					0% {opacity:1;}
					100% {opacity:0;}
				}

				@-moz-keyframes opacity {
					0% {opacity:1;}
					100% {opacity:0;}
				}

				@keyframes opacity {
					0% {opacity:1;}
					100% {opacity:0;}
				}
			}
		}
	}
</style>