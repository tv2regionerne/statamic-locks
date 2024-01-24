import LocksListing from './components/locks/Listing.vue'

Statamic.booting(() => {
    Statamic.component('statamic-locks-listing', LocksListing);
})
