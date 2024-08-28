<script setup lang="ts">
interface ImageFile {
  file: File;
  url: string;
}

const images = ref<ImageFile[]>([]);

const handleFileChange = (event: Event) => {
  const files = (event.target as HTMLInputElement).files;
  if (files) {
    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      const url = URL.createObjectURL(file);
      images.value.push({ file, url });
    }
  }
};

const removeImage = (index: number) => {
  URL.revokeObjectURL(images.value[index].url); // Revoke the object URL to free up memory
  images.value.splice(index, 1);
};

</script>
<template>
    <div>
        <input type="file" multiple accept="image/*" @change="handleFileChange" />
        <div v-if="images.length">
            <div v-for="(image, index) in images" :key="index" class="image-preview">
                <img :src="image.url" :alt="'Image ' + (index + 1)" />
                <button @click="removeImage(index)">Remove</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.image-preview {
  display: inline-block;
  margin: 10px;
  text-align: center;
}

.image-preview img {
  max-width: 100px;
  height: auto;
  display: block;
}

.image-preview button {
  margin-top: 5px;
  background-color: red;
  color: white;
  border: none;
  cursor: pointer;
}
</style>