<style scoped>
.gallery-controls {
	margin-bottom: 20px;
	text-align: center;
}

.image-grid {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 20px;
}

.image-item {
	position: relative;
}

.delete-btn {
	position: absolute;
	top: 10px;
	right: 10px;
	z-index: 10;
	background-color: red;
	color: white;
	border: none;
	padding: 5px 10px;
	cursor: pointer;
}

.delete-btn:hover {
	background-color: darkred;
}

.regenerate-btn {
	background-color: green;
	border-radius: 10px;
	color: white;
	border: none;
	padding: 5px 10px;
	cursor: pointer;
}

.regenerate-btn:hover {
	background-color: darkgreen;
}

.image-item img {
	width: 100%;
	height: 300px;
	object-fit: cover;
	cursor: pointer;
}
</style>

<template>
	<div class="gallery">
		<div class="gallery-controls">
			<button class="regenerate-btn" @click="regenerateImages">Regenerate Images</button>
		</div>
		<div class="image-grid">
			<div v-for="(img, index) in imgs" :key="img.url" class="image-item">
				<button @click="deleteImage(index, $event)" class="delete-btn">Remove</button>
				<img :src="img.url" @click="showImg(index)" />
			</div>
		</div>

		<vue-easy-lightbox :visible="visible" :imgs="imageUrls" :index="index" @hide="handleHide" />
	</div>
</template>

<script>
import Images from '@/assets/images.json';
import VueEasyLightbox from 'vue-easy-lightbox';

export default {
	components: {
		VueEasyLightbox
	},
	data() {
		return {
			originalImgs: Images.images,
			imgs: [...Images.images],
			visible: false,
			index: 0,
		};
	},
	computed: {
		imageUrls() {
			return this.imgs.map(img => img.url);
		}
	},
	methods: {
		showImg(index) {
			this.index = index;
			this.visible = true;
		},
		handleHide() {
			this.visible = false;
		},
		deleteImage(index, event) {
			event.stopPropagation(); // Stop the event from bubbling up to the parent div
			this.imgs.splice(index, 1); // Delete the image
		},

		regenerateImages() {
			this.imgs = [...this.originalImgs];
		}
	}
};
</script>
