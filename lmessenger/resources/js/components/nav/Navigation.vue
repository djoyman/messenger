<template>
	<div class="nav-container">
		<!-- <button class="menu" @click.prevent="registerSettingsClick" >
			<i class="fas fa-cog fa-lg"></i>
		</button> -->
		<h2 class="header">{{ room.title }}</h2>
		<button class="user-list" @click.prevent="registerUserListClick" >
			<i class="fas fa-users fa-lg"></i>
		</button>
		<!-- <a v-bind:href="route_logout" @click.prevent="logout()" class="nav-element">Logout</a>
		<form id="logout-form" v-bind:action="route_logout" method="POST" style="display: none;">
            <input type="hidden" name="_token" v-bind:value="getCSRF">
        </form> -->
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
		}
		// route_logout: {
		// 	type: String,
		// 	required: true
		// }
	},

	// computed: {
	// 	getCSRF() {
	// 		return document.head.querySelector('meta[name="csrf-token"]').content;
	// 	}
	// },

	methods: {
		// logout() {
		// 	document.getElementById('logout-form').submit();
		// }

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
		}
	}
</style>