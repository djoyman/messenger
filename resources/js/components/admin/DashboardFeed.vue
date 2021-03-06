<template>
	<div id="msg-scroll" class="feed" @scroll="onScrolling">
		<infinite-loading @infinite="infiniteHandler" direction="top" spinner="spiral" :identifier="identifier">
			<div class="inf-style" slot="no-more">Начало истории чата</div>
			<div class="inf-style" slot="no-results">Сообщений нет</div>
			<div class="inf-style" slot="error" slot-scope="{ trigger }">
				Ошибка загрузки <br><button @click="trigger">Повторить</button>
			</div>
		</infinite-loading>
		<div v-for="message in chatMessages" :key="message.id" class="message">
			<div class="content">
				<p @click="openOptions(message)" class="name">{{ message.name }} <span class="date">{{ getDateString(message.date) }} </span><span class="user-id">ID: {{ message.from }}</span></p>
				<p class="text">{{ message.content }}</p>
				<img class="image" v-if="message['attachment:source'].length > 0" @click.prevent="openPreview(message.attachment.source)" :src="`${ message['attachment:source'] }`" width="200" :height="`${ calcHeight(message['attachment:width'], message['attachment:height']) }`" onerror="this.onerror=null;this.src='/images/blank.jpg';" loading="lazy">
				<hr class="break">
			</div>
		</div>
		<button id="btn-scroll" class="scroll-bottom" @click.prevent="clickScrollBottom">
			<i class="fas fa-arrow-down fa-lg"></i>
		</button>
	</div>
</template>

<script>
import InfiniteLoading from 'vue-infinite-loading';

export default {
	props: {
		messages: {
			type: Array,
			default: [],
			required: true
		},
		identifier: {
			type: Number,
			required: true
		}
	},

	data() {
		return {
			chatMessages: this.messages,
			lastScroll: 0,
			btnPoint: false,
		}
	},

	methods: {
		infiniteHandler($state) {
			this.$emit('loadMoreMessages', $state);
		},

		calcHeight(width, height) {
			return height * (200 / width);
		},

		onScrolling() {
			const div = document.getElementById('msg-scroll');
			const btn = document.getElementById('btn-scroll');
			if (div.scrollHeight - div.scrollTop > div.clientHeight + 200) {
				btn.style.background = 'rgb(36, 53, 73)';
				btn.style.opacity = '1';
			} else {
				btn.style.opacity = '0';
			}
		},

		clickScrollBottom() {
			this.$emit('scrollBottomEvent');
			const btn = document.getElementById('btn-scroll');
			btn.style.background = 'rgb(69, 95, 125)';
		},

		getDateString(timestamp) {
			const date = new Date(parseInt(timestamp));

			const day = (date.getDate() < 10) ? `0${date.getDate()}` : date.getDate();
			const month = (date.getMonth() + 1 < 10) ? `0${(date.getMonth() + 1)}` : date.getMonth() + 1;
			const hours = (date.getHours() < 10) ? `0${date.getHours()}` : date.getHours();
			const minutes = (date.getMinutes() < 10) ? `0${date.getMinutes()}` : date.getMinutes();

			return `${ day }.${ month }.${ date.getFullYear() } ${ hours }:${ minutes }`;
		},

		openOptions(message) {
			this.$emit('optionsEvent', message);
		}
	},

	components: {
		InfiniteLoading
	}
}
</script>

<style lang="scss" scoped>

	ul {
		list-style-type: none;
		padding: 0;
	}

	hr {
		border-top: 1px solid rgba(79, 79, 79, 0.16);
	}

	.feed {
		background: rgb(25, 37, 51);
		color: #FFF;
		overflow-y: scroll;
		padding-bottom: 8px;
		margin-top: 16px;
		display: flex;
		flex-direction: column;
		height: 100vh;
		width: 100vw;
		position: relative;

		.inf-style {
			font-size: 12px;
			color: #7b7b7b;
			padding-top: 8px;
		}

		.message {
			padding: 8px 8px 0px 12px;
			text-align: left;

			.content {


				.text {
					white-space: pre-line;
					word-break: break-word;
					margin-bottom: 0;
					font-size: 14px;
				}

				.name {
					font-size: 14px;
					margin-bottom: 0;
					color: #ececec;
				}

				.date {
					font-size: 10px;
					color: #7b7b7b;
				}

				.user-id {
					font-size: 16px;
					color: #20b6df;
				}

				.image {
					width: 200px;
					margin-top: 12px;
					padding: 6px;
				}

				.break {
					margin: 0;
					margin-top: 8px;
				}
			}
		}

		.scroll-bottom {
			border-radius: 50%;
			border: none;
			outline: none;
			background: rgb(36, 53, 73);
			width: 52px;
			height: 52px;
			position: fixed;
			bottom: 72px;
			right: 24px;
			color: #fff;
			display: flex;
			align-items: center;
			justify-content: center;
			text-align: center;
			box-shadow: 0 0 8px rgba($color: #000000, $alpha: 0.15);
			opacity: 0;
			transition: opacity 300ms ease-out;
		}
	}

</style>