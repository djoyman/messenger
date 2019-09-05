<template>
	<div class="app-container">
		<Navigation :user="user" :room="room" @userListClickEvent="openSideBar('users')" @settingsClickEvent="openSideBar('settings')" />
		<ChatMessageFeed @loadMoreMessages="getMessageHistory" :messages="chatMessages" :user="user" :typing="isUserTyping" />
		<ChatMessageComposer @sendEvent="onRegisterNewMessage" @userTypingEvent="onTypingEvent" :users="chatUsers" />
		<ChatUsers :users="chatUsers" />
		<ChatSettings :user="user" />
		<div id="overlay" @click="hideSideBar" ></div>
	</div>
</template>

<script>
import Navigation from './nav/Navigation';
import ChatMessageFeed from './ChatMessageFeed';
import ChatMessageComposer from './ChatMessageComposer';
import ChatUsers from './ChatUsers';
import ChatSettings from './ChatSettings';
// import { clearTimeout, setTimeout } from 'timers';

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
				setTimeout(() => this.scrollToBottom(), 50);
			} )
			// .listenForWhisper( 'typingEvent', ( e ) => {

			// 	if ( this.timerFlag ) clearTimeout( this.timerFlag );
				
			// 	this.isUserTyping = true;

			// 	const data = {
			// 		name: e.name,
			// 		typing: true 
			// 	}

			// 	this.typingUsers = this.typingUsers.push(data).filter((v, i, a) => a.indexOf(v) === i);

			// 	console.log(this.typingUsers);

			// 	this.timerFlag = setTimeout( () => this.isUserTyping = false, 20000 );
			// } );
	},

	computed: {
		channel() {
			return window.Echo.join('chat_room.' + this.room.id);
		},

		getDateString() {
			const date = new Date();
			const day = (date.getDate() < 10) ? '0' + date.getDate() : date.getDate();
			const month = (date.getMonth() + 1 < 10) ? '0' + (date.getMonth() + 1) : date.getMonth() + 1;
			const hours = (date.getHours() < 10) ? '0' + date.getHours() : date.getHours();
			const minutes = (date.getMinutes() < 10) ? '0' + date.getMinutes() : date.getMinutes();

			return `${ day }.${ month }.${ date.getFullYear() } ${ hours }:${ minutes }`;
		}
	},

	methods: {
		onRegisterNewMessage(msgData) {

			this.messageText = msgData.message;
			
			const msg = {
				'date': this.getDateString,
				'name': this.user.name,
				'content': this.messageText,
				'from': this.user.id,
				'room_id': this.room.id,
				'attachment': msgData.image
			}

			fetch(`/api/messages?api_token=${this.token}`, {
				method: 'post',
				body: JSON.stringify(msg), 
				headers: {
					'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
					'Content-Type': 'application/json',
					'X-Socket-ID': window.Echo.socketId()
				}
			})
			.then( () => {
				this.chatMessages.push( msg );
				this.messageText = '';
				setTimeout(() => this.scrollToBottom(), 50);
			} )
			.catch( err => console.log( err ) );

		},

		// onTypingEvent() {
		// 	this.channel.whisper('typingEvent', {
		// 		name: this.user.name,
		// 	});
		// },

		getMessageHistory($state) {
			fetch(`/api/messages/history/${this.room.id}?api_token=${this.token}&page=${this.page}`, {
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
			document.getElementById('settings').style.width = '0';
			document.getElementById('overlay').style.visibility = 'hidden';
			document.getElementById('overlay').style.opacity = '0';
		}
	},

	components: {
		ChatMessageFeed, ChatMessageComposer, ChatUsers, Navigation, ChatSettings
	}
}
</script>

<style lang="scss" >
	
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
	}
</style>