import LocksListing from './components/locks/Listing.vue'
import LockModal from './components/modals/LockModal.vue'

Statamic.booting(() => {
    Statamic.component('statamic-locks-listing', LocksListing);
    Statamic.component('statamic-locks-modal', LockModal);
});

Statamic.booted(() => {
    let urlPath = ('/' + Statamic.$config.get('urlPath')).split('/');
    let locks = Statamic.$config.get('statamicLocks') ?? {};

    for (let handle in locks) {
        let path = locks[handle].split('/');

        let valid = true;
        let index = 0;
        let part = '';
        let id = '';
        while (valid && path.length) {
            part = path.shift();

            if (part == '{id}') {
                id = urlPath[index];
                continue;
            }

            if (! (part == '*' || urlPath[index] == part)) {
                valid = false;
                continue;
            }

            index++;

            if (path.length && (urlPath.length < index + 1)) {
                valid = false;
            }
        }

        if (valid && id && ! path.length) {
            Statamic.$components.append('statamic-locks-modal', {
                props: { itemType: handle, itemId: id }
            });
        }
    }
});
