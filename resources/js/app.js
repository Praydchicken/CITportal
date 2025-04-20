
import './bootstrap';
import '../css/app.css';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

import { createApp, h } from 'vue'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import { createInertiaApp, Head, Link } from '@inertiajs/vue3'




createInertiaApp({
  title: (title) => `${title} | CIT PORTAL`,
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    let page = pages[`./Pages/${name}.vue`]
    return page;
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .component('font-awesome-icon', FontAwesomeIcon)
      .component('Head', Head)
      .component('Link', Link)
      .mount(el)
  },

  progress: {
    color: 'yellow',

    // Whether to include the default NProgress styles...
    includeCSS: true,

    // Whether the NProgress spinner will be shown...
    showSpinner: false,

  },
})
// Clear flash messages on page load if they exist
window.addEventListener('beforeunload', () => {
  if (page.props.flash?.success || page.props.flash?.error) {
    Inertia.delete(route('admin.clear-flash'), {
      preserveScroll: true,
      preserveState: true,
    });
  }
});

