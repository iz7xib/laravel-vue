import {createStore} from "vuex";
import axiosClient from "../axios";

const tmpPosts = [
  {
    id: 100,
    user_id: 8,
    title: "Nuova esperienza con Vue e Laravel",
    category: 'coding',
    status: 'draft',
    image: 'https://images.agi.it/pictures/agi/agi/2022/06/26/155313171-b2c0b642-51c7-45b9-b8ae-b151c913bfe1.jpg',
    content: 'L\'annuncio del presidente americano dopo la prima sessione dei lavori del G7 in Baviera.',
    created_at: "2022-06-26 18:00:00",
    updated_at: "2022-06-26 18:00:00"
  },
  {
    id: 101,
    user_id: 9,
    title: "Nuova esperienza con Vue e Laravel 1 ",
    category: 'estero',
    status: 'public',
    image: 'https://images.agi.it/pictures/agi/agi/2022/06/26/073139493-9688e0ad-41d2-4eab-9355-15b03b651ae9.jpg',
    content: 'L\'annuncio del presidente americano dopo la prima sessione dei lavori del G7 in Baviera.',
    created_at: "2022-06-26 18:00:00",
    updated_at: "2022-06-26 18:00:00"
  }
]

const store = createStore({
  state: {
    user: {
      data: {},
      token: sessionStorage.getItem("TOKEN"),
    },
    currentPost: {
      data: {},
      loading: false,
    },
    notification: {
      show: false,
      type: 'success',
      message: ''
    },
    posts: [...tmpPosts],
  },
  getters: {},
  actions: {
    savePost({ commit, dispatch }, post) {
      delete post.image_url;

      let response;
      if (post.id) {
        response = axiosClient
          .put(`/post/${post.id}`, post)
          .then((res) => {
            commit('setCurrentPost', res.data)
            return res;
          });
      } else {
        response = axiosClient.post("/post", post).then((res) => {
          commit('setCurrentPost', res.data)
          return res;
        });
      }

      return response;
    },
    deletePost({ dispatch }, id) {
      return axiosClient.delete(`/post/${id}`).then((res) => {
        dispatch('getPosts')
        return res;
      });
    },
    register({ commit }, user){
      return axiosClient.post('/register', user)
        .then(({data}) => {
          commit('setUser', data);
          return data;
        })
    },
    login({ commit }, user){
      return axiosClient.post('/login', user)
        .then(({data}) => {
          commit('setUser', data);
          return data;
        })
    },
    logout({ commit }, user){
      return axiosClient.post('/logout', user)
        .then(response => {
          commit('logout')
          return response;
        })
    },
    getPost({ commit }, id) {
      commit("setCurrentPostLoading", true);
      return axiosClient
        .get(`/post/${id}`)
        .then((res) => {
          commit("setCurrentPost", res.data);
          commit("setCurrentPostLoading", false);
          return res;
        })
        .catch((err) => {
          commit("setCurrentPostLoading", false);
          throw err;
        });
    },
    getPosts({ commit }, {url = null} = {}) {
      commit('setPostsLoading', true)
      url = url || "/post";
      return axiosClient.get(url).then((res) => {
        commit('setPostsLoading', false)
        commit("setPosts", res.data);
        return res;
      });
    },
  },
  mutations:{
    logout: (state) => {
      state.user.token = null;
      state.user.data = {};
      sessionStorage.removeItem('TOKEN');
    },
    setUser: (state, userData) => {
      state.user.token = userData.token;
      state.user.data = userData.user;
      sessionStorage.setItem('TOKEN', userData.token);
    },
    savePost: (state, post) => {
      state.posts = [...state.posts, post.data]
    },
    updatePost: (state, post) => {
      state.posts = state.posts.map((p) => {
        if (p.id == post.data.id){
          return post.data;
        }
        return p;
      })
    },
    setPosts: (state, posts) => {
      state.posts.links = posts.meta.links;
      state.posts.data = posts.data;
    },
    setPostsLoading: (state, loading) => {
      state.posts.loading = loading;
    },
    setPostLoading: (state, loading) => {
      state.post.loading = loading;
    },
    setCurrentPostLoading: (state, loading) => {
      state.currentPost.loading = loading;
    },
    setCurrentPost: (state, post) => {
      state.currentPost.data = post.data;
    },
    notify: (state, {message, type}) => {
      state.notification.show = true;
      state.notification.type = type;
      state.notification.message = message;
      setTimeout(() => {
        state.notification.show = false;
      }, 3000)
    },
  },
  modules: {}
})

export default store;
