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
    posts: [...tmpPosts],
  },
  getters: {},
  actions: {
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
    }
  },
  modules: {}
})

export default store;
