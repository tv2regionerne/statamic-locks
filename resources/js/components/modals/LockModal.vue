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
                <button class="btn ml-4 btn-primary"
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
        }, 30000);
    },

    methods: {

        back() {
            window.history.back();
        },

        checkLockStatus() {
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
                this.status = [];
            }).catch(e => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            this.$toast.error(__('Error getting lock status'));
        },

        locks() {
            window.location.href = cp_route('statamic-locks.locks');
        }

    }

}
</script>
