import { createApp } from 'vue';
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import router from './routes/index'
import VueSweetalert2 from "vue-sweetalert2";
import { abilitiesPlugin } from '@casl/vue';
import ability from './services/ability';
import { installI18n, loadMessages } from "./plugins/i18n";
import { langStore } from "@/store/lang";
import './plugins/axios.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import '../css/app.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import App from './main.vue'

import PrimeVue from "primevue/config";
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import Column from 'primevue/column';
import ConfirmationService from 'primevue/confirmationservice';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import DialogService from 'primevue/dialogservice';
import Skeleton from 'primevue/skeleton';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import FileUpload from 'primevue/fileupload';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import Panel from 'primevue/panel';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Tooltip from 'primevue/tooltip';
import Ripple from 'primevue/ripple';
import ProgressBar from 'primevue/progressbar';

const app = createApp(App);

const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)

app.use(pinia)
app.use(router)
app.use(VueSweetalert2)
app.use(abilitiesPlugin, ability)
app.use(ToastService);
app.use(DialogService);
app.use(ConfirmationService);

installI18n(app);
const l = langStore();
l.$subscribe((_, state) => {
    loadMessages(state.locale)
});

import Aura from '@primevue/themes/aura';
app.use(PrimeVue, {
    ripple: true,
    theme: {
        preset: Aura,
        options: {
            prefix: 'p',
            darkModeSelector: '.app-dark',
            cssLayer: false
        }
    }
});


app.component('Avatar', Avatar);
app.component('Button', Button);
app.component('DataTable', DataTable);
app.component('Column', Column);
app.component('Dialog', Dialog);
app.component('Skeleton', Skeleton);
app.component('Toast', Toast);
app.component('FileUpload', FileUpload);
app.component('InputText', InputText);
app.component('MultiSelect', MultiSelect);
app.component('Panel', Panel);
app.component('Card', Card);
app.component('Tag', Tag);
app.component('ProgressBar', ProgressBar);

app.directive('tooltip', Tooltip);
app.directive('ripple', Ripple);

(function initDarkMode() {
    try {
        const savedTheme = localStorage.getItem('styleStore');
        if (savedTheme) {
            const themeData = JSON.parse(savedTheme);
            const isDark = themeData?.darkTheme === true || (themeData?.state && themeData.state.darkTheme === true);
            if (isDark) {
                document.documentElement.classList.add('app-dark', 'dark');
                document.body.classList.add('dark');
                return;
            }
        }
    } catch (e) {
        console.error('Error al leer tema:', e);
    }

    document.documentElement.classList.remove('app-dark', 'dark');
    document.body.classList.remove('dark');
})();

app.mount('#app')
