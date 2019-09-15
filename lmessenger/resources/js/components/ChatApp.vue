<template>
	<div class="app-container">
		<Navigation :user="user" :room="room" :typing="typingUsers" @userListClickEvent="openSideBar('users')" @settingsClickEvent="openSideBar('settings')" />
		<ChatMessageFeed @loadMoreMessages="getMessageHistory" @onPreviewClick="makePreview" @scrollBottomEvent="scrollToBottom" :messages="chatMessages" :user="user" />
		<ChatMessageComposer @sendEvent="onRegisterNewMessage" @userTypingEvent="onTypingEvent" :users="chatUsers" />
		<ChatUsers :users="chatUsers" />
		<!-- <ChatSettings :user="user" /> -->
		<div id="overlay" @click="hideSideBar" ></div>
		<div id="img-preview" style="display:none;">
			<button id="preview-close">Закрыть</button>
			<div class="img-wrapper">
				<img id="img-src" src="#">
			</div>
		</div>
	</div>
</template>

<script>
import Navigation from './nav/Navigation';
import ChatMessageFeed from './ChatMessageFeed';
import ChatMessageComposer from './ChatMessageComposer';
import ChatUsers from './ChatUsers';
import ChatSettings from './ChatSettings';
import Hammer from 'hammerjs';
import { clearTimeout, setTimeout } from 'timers';

export default {
	props: {
		token: { 
			type: String,
			required: true,
		},
		room: {
			type: Object,
			required: true,
		},
		user: {
			type: Object,
			required: true,
		},
	},

	data() {
		return {
			chatUsers: [],
			chatMessages: [],
			typingUsers: [],
			messageText: '',
			timerFlag: false,
			page: 1,
		}
	},

	created() {
		
		this.channel
			.here( users => this.chatUsers = users )

			.joining( user => this.chatUsers.push( user ) )

			.leaving( user => this.chatUsers = this.chatUsers.filter( value => value.id !== user.id ))

			.listen( 'SendMessage', ({ data }) => {
				this.chatMessages.push( data );
			} )
			.listenForWhisper( 'typingEvent', ( e ) => {

				const timerLen = 1000;

				if (e.id === this.user.id) return;
				
				const userObj = {
					id: e.id,
					name: e.name,
					timerId: null,
				}

				this.typingUsers.push(userObj);
				this.typingUsers = this.typingUsers.reduce((acc, x) =>
					acc.concat(acc.find(y => y.id === x.id) ? [] : [x]), []
				);

				this.typingUsers.forEach((v, i) => {
					if (v.name === userObj.name){
						if (v.timerId !== null) {
							clearTimeout( v.timerId );
							v.timerId = setTimeout( () => {
								this.typingUsers.splice(i, 1);
							}, timerLen );
						} else {
							v.timerId = setTimeout( () => { 
								this.typingUsers.splice(i, 1); 
							}, timerLen );
						}
					}
				});

			} );

		setTimeout(() => this.scrollToBottom(), 50 );

		// window.Echo.connector.socket.on('connect', function(){
		// 	console.log('connected', window.Echo.socketId());
		// });
		// window.Echo.connector.socket.on('disconnect', function(){
		// 	console.log('disconnected');
		// });
		// window.Echo.connector.socket.on('reconnecting', function(attemptNumber){
		// 	console.log('reconnecting', attemptNumber);
		// });
	},

	computed: {
		channel() {
			return window.Echo.join('chat_room.' + this.room.id);
		},
	},

	methods: {
		onRegisterNewMessage(msgData) {

			this.messageText = msgData.message;
			
			const msg = {
				'date': this.getDateString(),
				'name': this.user.name,
				'content': this.messageText,
				'from': this.user.id,
				'room_id': this.room.id,
				'attachment': msgData.image
			}

			fetch(`../api/messages?api_token=${this.token}`, {
				method: 'post',
				body: JSON.stringify(msg), 
				headers: {
					'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
					'Content-Type': 'application/json',
					'X-Socket-ID': window.Echo.socketId()
				}
			})
			.then( (res) => {
				// 429 - Too many requests
				if (res.status === 429) {
					alert('Too many attempts! Stop spamming');
					return;
				}
				this.chatMessages.push( msg );
				this.messageText = '';
				setTimeout(() => this.scrollToBottom(), 0);
			} )
			.catch( err => {
				console.log( err );
				alert('Unable to send message!');
			} );

		},

		onTypingEvent() {
			this.channel.whisper('typingEvent', {
				id: this.user.id,
				name: this.user.name,
			});
		},

		getMessageHistory($state) {
			fetch(`../api/messages/history/${this.room.id}?api_token=${this.token}&page=${this.page}`, {
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

		openSideBar(bar) {
			document.getElementById(bar).style.width = '280px';
			document.getElementById('overlay').style.visibility = 'visible';
			document.getElementById('overlay').style.opacity = '1';
		},

		hideSideBar() {
			document.getElementById('users').style.width = '0';
			// document.getElementById('settings').style.width = '0';
			document.getElementById('overlay').style.visibility = 'hidden';
			document.getElementById('overlay').style.opacity = '0';
		},

		makePreview(src) {
			const container = document.getElementById('img-preview');
			container.style.display = 'flex';

			const img = document.getElementById('img-src');
			img.style.maxWidth = '100%';
			img.style.maxHeight = '100%';
			img.src = src;
			
			const btn = document.getElementById('preview-close');
			btn.addEventListener('click', function() {
				container.style.display = 'none';
				img.src = '';
			});

			const hammertime = new Hammer(img, {});
			hammertime.get('pinch').set({
				enable: true
			});
			let posX = 0,
				posY = 0,
				scale = 1,
				last_scale = 1,
				last_posX = 0,
				last_posY = 0,
				max_pos_x = 0,
				max_pos_y = 0,
				transform = '',
				el = img;

			hammertime.on('doubletap pan pinch panend pinchend', function(ev) {
				if (ev.type == "doubletap") {
					transform =
						'translate3d(0, 0, 0) ' +
						'scale3d(2, 2, 1) ';
					scale = 2;
					last_scale = 2;
					try {
						if (window.getComputedStyle(el, null).getPropertyValue('-webkit-transform').toString() != 'matrix(1, 0, 0, 1, 0, 0)') {
							transform =
								'translate3d(0, 0, 0) ' +
								'scale3d(1, 1, 1) ';
							scale = 1;
							last_scale = 1;
						}
					} catch (err) {}
					el.style.webkitTransform = transform;
					transform = '';
				}

				//pan    
				if (scale != 1) {
					posX = last_posX + ev.deltaX;
					posY = last_posY + ev.deltaY;
					max_pos_x = Math.ceil((scale - 1) * el.clientWidth / 2);
					max_pos_y = Math.ceil((scale - 1) * el.clientHeight / 2);
					if (posX > max_pos_x) {
						posX = max_pos_x;
					}
					if (posX < -max_pos_x) {
						posX = -max_pos_x;
					}
					if (posY > max_pos_y) {
						posY = max_pos_y;
					}
					if (posY < -max_pos_y) {
						posY = -max_pos_y;
					}
				}

				//pinch
				if (ev.type == 'pinch') {
					scale = Math.max(.999, Math.min(last_scale * (ev.scale), 4));
				}
				if(ev.type == 'pinchend') {
					last_scale = scale;
				}

				//panend
				if(ev.type == 'panend'){
					last_posX = posX < max_pos_x ? posX : max_pos_x;
					last_posY = posY < max_pos_y ? posY : max_pos_y;
				}

				if (scale != 1) {
					transform =
						`translate3d(${ posX }px,${ posY }px, 0) 
						scale3d(${ scale },${ scale }, 1)`;
				}

				if (transform) {
					el.style.webkitTransform = transform;
				}
			});
		},

		getDateString() {
			const date = new Date();
			const time_zone = (('<? echo time();?>' - loc/1000)/60).toFixed(0);
			const day = (date.getDate() < 10) ? '0' + date.getDate() : date.getDate();
			const month = (date.getMonth() + 1 < 10) ? '0' + (date.getMonth() + 1) : date.getMonth() + 1;
			const hours = (date.getHours() < 10) ? '0' + date.getHours() : date.getHours();
			const minutes = (date.getMinutes() < 10) ? '0' + date.getMinutes() : date.getMinutes();

			return `${ day }.${ month }.${ date.getFullYear() } ${ hours }:${ minutes }`;
		}
	},

	components: {
		ChatMessageFeed, ChatMessageComposer, ChatUsers, Navigation, ChatSettings
	}
}
</script>

<style lang="scss" >

	$primaryBg: rgb(25, 37, 51);

	body {
		background: $primaryBg;
	}
	
	.app-container {
		height: 100vh;
		display: flex;
		flex-direction: column;
		justify-content: stretch;

		#overlay {
			width: 100%;
			height: 100%;
			position: absolute;
			top: 0;
			left: 0;
			z-index: 2;
			background: rgba($color: #000000, $alpha: 0.75);
			transition: visibility 0.5s, opacity 0.5s ease-out;
			visibility: hidden;
			opacity: 0;
		}

		#img-preview {
			width: 100%;
			height: 100%;
			position: absolute;
			top: 0;
			left: 0;
			z-index: 2;
			background: #000;

			#preview-close {
				position: fixed;
				left: 0;
				top: 0;
				width: 100%;
				z-index: 3;
				background: rgba($color: #000000, $alpha: 0.7);
				border: none;
				color: #fff;
				padding: 10px;
			}

			.img-wrapper {
				width: inherit;
				height: inherit;

				display: flex;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				text-align: center;

				#img-src {
					transition: scale 150ms linear;
				}
			}
		}
	}
</style>