<template>
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<label for="token">API Token</label>
				<input class="form-control" type="text" name="token" v-model="api_token">
			</div>
		</div>
	</div>
</template>

<script>
export default {
	props: ['token'],
	
	data() {
		return {
			api_token: this.token,
		}
	},

	methods: {

	}
}
</script>