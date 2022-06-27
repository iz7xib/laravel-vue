<template>
<PageComponent>
  <template v-slot:header>
    <div class="flex items-center justify-between">
      <h1 class=" text-3xl font-bold text-gray-900">
        {{model.id ? model.title : "Create a post"}}
      </h1>
    </div>
  </template>

  <form @submit.prevent="savePost">
    <div class="shadow sm:rounded-md sm:overflow-hidden">
      <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Image
          </label>
          <div class="mt-1 flex items-center">
            <img
              v-if="model.image_url"
              :src="model.image_url"
              :alt="model.title"
              class="w-64 h48 object-cover"/>
            <span v-else class="flex items-center justify-center h-12 w-12 rounded-full overflow-hidden bg-gray-100">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-[80%] w-[80%] text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </span>
            <button type="button" class="relative overflow-hidden ml-5 bg-white
            py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm
            leading-4 font-medium text-gray-700 hover:bg-gray-50
            focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              <input
                type="file"
                @change="onImageChoose"
                class="absolute left-0 top-0 right-0 bottom-0 opacity-0 cursor-pointer"
              />
              Change
            </button>
          </div>
        </div>
<!-- title-->
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
          <input type="text" name="title" id="title" v-model="model.title" autocomplete="post_title"
                 class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full
                 shadow-sm sm:text-sm border-gray-300 rounded-md"/>
        </div>
<!-- title end-->
        <!-- Description -->
        <div>
          <label for="about" class="block text-sm font-medium text-gray-700">
            Description
          </label>
          <div class="mt-1">
              <textarea
                id="content"
                name="content"
                rows="3"
                v-model="model.content"
                autocomplete="post_content"
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"
                placeholder="Content"
              />
          </div>
        </div>
        <!-- Description -->
        <!-- Category -->
        <div>
          <label for="about" class="block text-sm font-medium text-gray-700">
            Category
          </label>
          <div class="mt-1">
              <input type="text" name="category" id="category" v-model="model.category" autocomplete="post_category"
                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full
                 shadow-sm sm:text-sm border-gray-300 rounded-md"/>
          </div>
        </div>
        <!-- Category -->
        <!-- Status -->
        <div class="flex items-start">
          <div class="flex items-center h-5">
            <input
              id="status"
              name="status"
              type="checkbox"
              v-model="model.status"
              class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
            />
          </div>
          <div class="ml-3 text-sm">
            <label for="status" class="font-medium text-gray-700"
            >Active</label
            >
          </div>
        </div>
        <!--/ Status -->
        <!--save -->
        <div class="px-4 py-3 bg-gray-50 text-right sm:x-6">
          <button type="submit"
                  class="inline-flex justify-center py-2 px-4
                  border border-transparent shadow-sm text-sm font-medium
                  rounded-md text-white bg-indigo-600
                  hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Save
          </button>
        </div>
        <!--/ save -->
      </div>
    </div>
  </form>
</PageComponent>
</template>

<script setup>
import store from "../store";
import {computed, ref, watch} from "vue";
import {useRoute} from "vue-router";
import PageComponent from "../components/PageComponent.vue";
import router from "../router";

const route = useRoute();
const postLoading = computed(() => store.state.currentPost.loading);

let model = ref({
  title: '',
  status: false,
  content: null,
  image: null,
  category: null,
  created_by: null,
  user_id: null,
});
// Watch to current survey data change and when this happens we update local model
watch(
  () => store.state.currentPost.data,
  (newVal, oldVal) => {
    model.value = {
      ...JSON.parse(JSON.stringify(newVal)),
      status: !!newVal.status,
    };
  }
);
// If the current component is rendered on survey update route we make a request to fetch survey
if (route.params.id) {
  store.dispatch("getPost", route.params.id);
}
function onImageChoose (ev) {
  const file = ev.target.files[0];
  const reader = new FileReader();
  reader .onload = () => {

    model.value.image = reader.result;
    model.value.image_url = reader.result;
  };
  reader.readAsDataURL(file);
}
/**
 * Create or update post
 */
function savePost() {
  let action = "created";
  if (model.value.id) {
    action = "updated";
  }
  store.dispatch("savePost", { ...model.value }).then(({ data }) => {
    store.commit("notify", {
      type: "success",
      message: "The post was successfully " + action,
    });
    router.push({
      name: "PostView",
      params: { id: data.data.id },
    });
  });
}
function deletePost() {
  if (confirm(`Are you sure you want to delete this survey? Operation can't be undone!!`)) {
    store.dispatch("deletePost", model.value.id).then(() => {
      router.push({
        name: "Posts",
      });
    });
  }
}
</script>

<style scoped>

</style>
