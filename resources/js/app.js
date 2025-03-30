
import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue'
import {ZiggyVue} from '../../vendor/tightenco/ziggy'
import { createInertiaApp, Head, Link } from '@inertiajs/vue3'


createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    let page = pages[`./Pages/${name}.vue`]
    return page;
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .component('Head', Head)
			.component('Link', Link)
      .mount(el)
  },

  progress:{
    color: 'yellow',

    // Whether to include the default NProgress styles...
    includeCSS: true,

    // Whether the NProgress spinner will be shown...
    showSpinner: false,

  },
})