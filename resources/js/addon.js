import LocksListing from './components/locks/Listing.vue'
import LockModal from './components/modals/LockModal.vue'

Statamic.booting(() => {
    Statamic.component('statamic-locks-listing', LocksListing);
    Statamic.component('statamic-locks-modal', LockModal);
})
