<template>
	<div id="msg-scroll" class="feed">
		<div v-for="message in chatMessages" :key="message.id" class="message">
			<div class="content">
				<p class="name">{{ message.name }} <span class="date">{{ message.date }}</span></p>
				<p class="text">{{ message.content }}</p>
				<img class="image" onerror="this.onerror=null;this.src='/images/blank.jpg';" @click.prevent="openPreview(message.attachment)" v-if="message.attachment.length > 0" :src="`${ message.attachment }`">
				<hr class="break">
			</div>
		</div>
		<infinite-loading @infinite="infiniteHandler" direction="top" spinner="spiral">
			<div class="inf-style" slot="no-more">Начало истории чата</div>
			<div class="inf-style" slot="no-results">Сообщений нет</div>
			<div class="inf-style" slot="error" slot-scope="{ trigger }">
				Ошибка загрузки <br><button @click="trigger">Повторить</button>
			</div>
		</infinite-loading>
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
		user: {
			type: Object,
			required: true
		}
	},

	data() {
		return {
			chatMessages: this.messages,
			typingUsers: [],
		}
	},

	methods: {
		infiniteHandler($state) {
			this.$emit('loadMoreMessages', $state);
		},

		openPreview(src) {
			this.$emit('onPreviewClick', src);
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
		margin-top: 60px;
		display: flex;
		flex-direction: column-reverse;
		height: 100%;

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

				.image {
					width: 300px;
					margin-top: 12px;
					padding: 6px;
				}

				.break {
					margin: 0;
					margin-top: 8px;
				}
			}
		}	
	}

</style>