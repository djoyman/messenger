<template>
	<div class="message-composer">
		<div :class="`image-preview ${isImageAppended ? '' : 'hidden'}`">
			<div class="preview-container">
				<button v-on:click.prevent="clearImageData" id="btn-remove-img">X</button>
				<img id="preview" src="#"/>
				<canvas id="img" class="hidden"></canvas>
			</div>
		</div>
		<div id="composer-wrapper" class="main-composer">
			<div v-if="isUploadSupported" class="image-upload">
				<label for="file">
					<i class="fas fa-camera fa-lg"></i>
				</label>
				<input v-on:change.prevent="onInputFile" id="file" type="file" accept="image/jpeg,image/jpg,image/png">
			</div>
			<textarea id="msg-text" v-model="message" class="composer" placeholder="Написать..." @input="onInputText"></textarea>
			<button id="btn-send" class="btn-send" @click.prevent="emitSendEvent">
				<i class="fas fa-envelope fa-lg"></i>
			</button>
		</div>
	</div>
</template>

<script>
export default {
	props: {
		users: {
			type: Array,
			required: true
		}
	},

	data() {
		return {
			message: '',
			isImageAppended: false,
			imageData: '',
		}
	},

	computed: {
		isUploadSupported() {
			if (navigator.userAgent.match(/(Android (1.0|1.1|1.5|1.6|2.0|2.1))|(Windows Phone (OS 7|8.0))|(XBLWP)|(ZuneWP)|(w(eb)?OSBrowser)|(webOS)|(Kindle\/(1.0|2.0|2.5|3.0))/)) {
				return false;
			}
			return true;
		}
	},

	methods: {
		emitSendEvent() {			
			if ( this.message === '' ) {
				if (this.imageData === '') {
					return;
				}
			}

			const data = {
				message: this.message,
				image: this.imageData
			}

			this.$emit('sendEvent', data);
			this.message = '';
			this.clearImageData();
			this.updateTextArea(true);
		},

		updateTextArea(sent) {
			const text = document.getElementById('msg-text');
			text.style.height = 'auto';
			text.style.height = (!sent) ? text.scrollHeight + 'px' : '50px';
			const container = document.getElementById('composer-wrapper');
			container.style.height = 'auto';
			container.style.height = (!sent) ? text.style.height : '50px';
			container.style.alignItems = (!sent && text.scrollHeight > 60) ? 'flex-start' : 'center';
		},

		// emitTypingEvent() {
		// 	this.$emit('userTypingEvent');
		// },

		onInputText(e) {
			const btn = document.getElementById('btn-send');
			const textarea = document.getElementById('msg-text');
			if (textarea.value.length > 0) {
				btn.className = 'btn-send bright';
			} else {
				btn.className = 'btn-send';
			}

			this.updateTextArea(false);
			
		},

		onInputFile(event) {
			if (window.File && window.FileReader && window.FormData) {

				const file = event.target.files[0];

				document.getElementById('btn-send').className = 'btn-send bright';

				if (file) {
					if (/^image\//i.test(file.type)) {
						this.readFile(file);
						this.isImageAppended = true;
					} else {
						alert('Not a valid image!');
					}
				}
			} else {
				alert("File upload is not supported!");
			}
		},

		readFile(file) {
			const reader = new FileReader();
			const progress = document.getElementById('progress');

			reader.onloadend = (e) => {
				document.getElementById('preview').src = e.target.result;
				this.processFile(reader.result, file.type);
			}

			reader.onerror = () =>
				alert('There was an error reading the file!');

			reader.readAsDataURL(file);
		},

		processFile(dataURL, fileType) {
			const maxWidth = 1920;
			const maxHeight = 1080;

			const image = new Image();
			image.src = dataURL;

			image.onload = () => {
				let width = image.width;
				let height = image.height;
				const shouldResize = (width > maxWidth) || (height > maxHeight);

				if (shouldResize) {

					if (width > height) {
						height = height * (maxWidth / width);
						width = maxWidth;
					} else {
						width = width * (maxHeight / height);
						height = maxHeight;
					}
				}

				const canvas = document.getElementById('img');

				canvas.width = width;
				canvas.height = height;

				const context = canvas.getContext('2d');

				context.drawImage(image, 0, 0, width, height);

				dataURL = canvas.toDataURL(fileType);

				this.imageData = dataURL;
			};

			image.onerror = () =>
				alert('There was an error processing your file!');
		},

		clearImageData() {
			
			const canvas = document.getElementById('img');
			canvas.width = 0;
			canvas.height = 0;

			const context = canvas.getContext('2d');
			context.clearRect(0, 0, canvas.width, canvas.height);

			document.getElementById('preview').src = '#';
			document.getElementById('file').value = '';
			document.getElementById('btn-send').className = 'btn-send';
			this.isImageAppended = false;
			this.imageData = '';
		}
	},

	
}
</script>

<style lang="scss" scoped>
	$primaryBg: rgb(25, 37, 51);
	$secondaryBg: rgb(19, 29, 41);
	$iconColor: rgb(40, 51, 64);

	.message-composer {
		position: relative;
		display: flex;
		flex-direction: column;
		justify-content: stretch;

		.image-preview {
			background: $secondaryBg;
			height: 120px;
			display: flex;
			justify-content: flex-start;
			align-items: center;
			padding: 12px 8px 8px 16px;

			.preview-container {
				text-align: center;
				position: relative;

				#btn-remove-img {
					position: absolute;
					width: 22px;
					height: 22px;
					right: 0;
					top: 0;
					background: #000;
					color: #fff;
					border: none;
					border-radius: 50%;
					outline: none;
				}

				#preview {
					height: 80px;
					border-radius: 5px;
				}
			}
		}

		.hidden {
			display: none;
		}

		.main-composer {
			display: flex;
			align-items: center;
			max-height: 300px;
			background: $primaryBg;
			box-shadow: 0 -1px 2px rgba($color: #000000, $alpha: 0.08);
			z-index: 1;
			position: relative; 
			
			.composer {
				width: calc(100% - 120px);
				max-height: inherit;
				height: 50px;
				overflow-y: hidden;
				padding: 4px 4px 4px 8px;
				resize: none;
				outline: none;
				text-decoration: none;
				background: $primaryBg;
				color: #fff;
				border: none;
			}

			.image-upload {
				width: 60px;
				background: $primaryBg;
				color: $iconColor;
				border: none;
				border-right: 1px $iconColor solid;
				padding: 4px;
				margin: 4px 0;
				outline: none;
				display: flex;
				text-align: center;
				justify-content: center;
				align-items: center;

				label {
					margin: 0;
				}

				#file {
					display: none;
				}
			}

			.btn-send {
				width: 60px;
				background: $primaryBg;
				color: $iconColor;
				border: none;
				border-left: 1px $iconColor solid;
				padding: 4px;
				margin: 4px 0;
				outline: none;

				&.bright {
					color: rgb(64, 212, 235);
				}
			}
		}
	}
</style>