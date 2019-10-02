<template>
	<div class="admin-container">
		<div class="top-wrapper">
			<div class="dropdown">
				<button class="dropbtn">Channels</button>
				<div id="channel-list" class="dropdown-content" v-if="channels.length > 0">
					<button v-for="channel in channels" :key="channel.id" @click="switchRoom( channel.title.match(/\d+/)[0] )">{{ channel.alias }}</button>
				</div>
			</div>
			<div class="refresh-wrapper">
				<button @click="getChannels" class="btn-refresh">
					<i class="fas fa-sync"></i>
				</button>
			</div>
		</div>
		<DashboardFeed ref="feed" @loadMoreMessages="getMessageHistory" @scrollBottomEvent="scrollToBottom" @optionsEvent="openDialog" :identifier="identifier" :messages="chatMessages"/>
		<div class="options" id="box">
			<table>
				<thead>
					<tr><th>ID</th><th>Имя</th><th>ВК</th></tr>
				</thead>
				<tbody>
					<tr><td>{{ user.id }}</td><td>{{ user.name }}</td><td>{{ user.social }}</td></tr>
				</tbody>
			</table>
			<button @click="banUser">Забанить</button>
			<button @click="closeDialog">Закрыть</button>
		</div>
	</div>
</template>

<script>
import DashboardFeed from './DashboardFeed';

export default {
	props: ['token'],

	data() {
		return {
			channels: [],
			user: {},
			room_id: 1,
			chatMessages: [],
			page: 1,
			identifier: Date.now(),
		}
	},

	mounted() {
		this.getChannels();
	},

	methods: {
		getChannels() {
			fetch(`../api/admin/channels?api_token=${ this.token }`, {
				method: 'get',
				headers: {
					'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
					'Content-Type': 'application/json',
				}
			})
			.then(res => res.json())
			.then(data => {
				
				this.channels = data;
			})
			.catch(err => console.log(err));
		},

		getUser(id) {
			return new Promise((resolve, reject) => {
				fetch(`../api/admin/users/${ id }?api_token=${ this.token }`, {
					method: 'get',
					headers: {
						'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
						'Content-Type': 'application/json',
					}
				})
				.then(res => res.json())
				.then(data => {

					resolve(data[0]);

				})
				.catch(err => { console.log(err); reject(err) });
			});
		},

		banUser() {
			alert(`Забанить пользователя ID: ${ this.user.id } Имя: ${ this.user.name }?`);
			fetch(`../api/admin/users/ban/${ this.room_id }/${ this.user.id }?api_token=${ this.token }`, {
				method: 'put',
				headers: {
					'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
					'Content-Type': 'application/json',
				}
			})
			.then(res => res.json())
			.then(data => {

				alert(data.result);

			})
			.catch(err => { console.log(err); alert(data.error) });
		},

		getMessageHistory($state) {
			fetch(`../api/messages/history/${this.room_id}?api_token=${this.token}&page=${this.page}`, {
				method: 'get',
				headers: {
					'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
					'Content-Type': 'application/json',
					'X-Socket-ID': window.Echo.socketId()
				}
			})
			.then( response => response.json() )
			.then( json => json.data) 
			.then( data => {
				if (data.length) {
					data.forEach(obj => this.chatMessages.unshift( obj ));
					this.page += 1;
					$state.loaded();
				} else {
					$state.complete();
				}
			} )
			.catch( err => {
				console.log( err );
				$state.error();
			} );
		},

		scrollToBottom() {
			const div = document.getElementById('msg-scroll');
			div.scrollTop = div.scrollHeight;
		},

		switchRoom(room) {
			this.room_id = parseInt(room);
			while(this.chatMessages.length > 0) {this.chatMessages.pop();}
			this.page = 1;
			this.identifier = Date.now();
		},

		openDialog(message) {
			const box = document.getElementById('box');

			this.getUser(parseInt(message.from))
			.then( user => {
				this.user = {
					id: user.id,
					name: user.name,
					social: user.social_id
				}

				box.style.display = 'block';

			} )
			.catch(err => console.log(err));
		},

		closeDialog() {
			document.getElementById('box').style.display = 'none';
			this.user = {}
		}
	},

	components: { DashboardFeed }
}
</script>

<style lang="scss" scoped>
	.admin-container {
		display: flex;
		justify-content: center;
		align-items: center;
		flex-direction: column;
		position: relative;

		.top-wrapper {
			display: flex;
			justify-content: flex-start;
			align-items: center;
			width: 100%;
		}

		.dropdown {
			position: relative;
			width: 90%;
			display: flex;
			flex-direction: column;

			.dropbtn {
				background-color: #4CAF50;
				color: white;
				padding: 16px;
				font-size: 16px;
				border: none;
				outline: none;
			}

			.dropdown-content {
				display: none;
				background-color: #f1f1f1;
				min-width: 160px;
				box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
				width: 100%;

				button {
					color: #fff;
					padding: 12px 16px;
					background-color: #111;
					text-decoration: none;
					display: block;
					border: none;
					outline: none;
					width: inherit;

					&:hover {
						background-color: #333;
					}
				}
			}

			&:hover {
				.dropdown-content {
					display: block;
				}
				.dropbtn {
					background-color: #3e8e41;
				}
			}
		}

		.refresh-wrapper {
			width: 20%;
			text-align: center;

			.btn-refresh {
				color: #fff;
				border: none;
				outline: none;
				background: none;
				padding: 16px;
				font-size: 16px;
			}
		}

		.options {
			position: absolute;
			width: 300px;
			background: #000;
			color: #fff;
			text-align: center;
			padding: 12px;
			font-size: 18px;
			display: none;

			table {
				margin: 0 auto;
				margin-bottom: 12px;
			}

			button:nth-child(2) {
				background: rgb(137, 6, 6);
				border: none;
				outline: none;
				color: #fff;
				height: 48px;
				margin-bottom: 12px;
			}

			button:nth-child(3) {
				background: rgb(64, 63, 63);
				border: none;
				outline: none;
				color: #fff;
				height: 48px;
			}
		}

	}
</style>