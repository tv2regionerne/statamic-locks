<template>

    <div>

        <modal class="modal-lock" v-if="show">
            <div class="flex items-center p-6 bg-gray-200 border-b text-center">
                {{ __('Item Locked') }}
            </div>

            <div class="p-6">
                <p class="text-base">{{ __('Locked by :name, last updated :since', {name: status.locked_by.name, since: status.last_updated}) }}
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
    },

    methods: {

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

    }

}
</script>
