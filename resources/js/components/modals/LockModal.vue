<template>

    <div>

        <modal class="modal-lock" v-if="show">
            <div class="flex items-center p-6 bg-gray-200 border-b text-center">
                {{ __('Item Locked') }}
            </div>

            <div class="p-6">
                <p class="text-base">{{ __('Locked by :name, last updated :since', {name: status.locked_by.name, since: status.last_updated}) }}
            </div>

            <div class="p-4 bg-gray-200 border-t flex items-center justify-between text-sm">
                <button class="btn"
                    @click="back"
                    v-text="__('Back')" />
                <button class="btn btn-danger"
                    @click="deleteLock"
                    v-text="__('Delete lock')"
                    v-if="this.can('delete user locks')" />
                <button class="btn btn-primary"
                    @click="locks"
                    v-text="__('Show locks')"
                    v-if="this.can('view locks')" />
            </div>
        </modal>

    </div>

</template>

<script>
export default {

    props: {
        itemId: {
            type: String,
            required: true
        },
        itemType: {
            type: String,
            required: true
        },
    },

    data() {
        return {
            show: false,
            status: [],
        }
    },

    mounted() {
        this.checkLockStatus();
        setInterval(() => {
            this.checkLockStatus();
        }, 1000 * Statamic.$config.get('statamicLocks.pollInterval', 30));

        window.addEventListener('beforeunload', () => {
            if (! this.show) {
                this.$axios.delete(cp_url('statamic-locks/locks/' + this.status.lock_id + '?delay=true'))
                    .then(response => { })
                    .catch(e => this.handleAxiosError(e));
            }
        });
    },

    methods: {

        back() {
            window.history.back();
        },

        deleteLock() {
            if (! this.status.lock_id) {
                this.$toast.error(__('This isnt possible'));
            }

            this.$axios.delete(cp_url('statamic-locks/locks/' + this.status.lock_id)).then(response => {
                window.location.reload();
            }).catch(e => this.handleAxiosError(e));
        },

        checkLockStatus() {
            this.updateCsrfToken().then(() => {
                this.$axios.post(cp_url('statamic-locks/locks'), {
                    item_id: this.itemId,
                    item_type: this.itemType,
                }).then(response => {
                    if (response.data.error) {
                        this.show = true;
                        this.status = response.data;
                        return;
                    }

                    this.show = false;
                    this.status = response.data;
                }).catch(e => this.handleAxiosError(e));
            });
        },

        handleAxiosError(e) {
            this.$toast.error(__('Error getting lock status'));
        },

        locks() {
            window.location.href = cp_url('statamic-locks/locks');
        },

        updateCsrfToken() {
            return this.$axios.get(cp_url('auth/token')).then(response => {
                const csrf = response.data;
                this.$axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
                this.$config.set('csrfToken', csrf);
            });
        },
    }

}
</script>
