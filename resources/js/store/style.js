import { ref } from "vue";
import { defineStore } from "pinia";

export const
    styleStore = defineStore("styleStore", () => {

        let darkTheme = ref(false);

        function setDarkTheme(is_dark) {
            darkTheme.value = is_dark;
        }
        function getDarkTheme() {
            return darkTheme.value;
        }

        return { darkTheme, setDarkTheme,getDarkTheme};
    }, {persist: true});

