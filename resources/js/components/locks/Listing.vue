<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="items">
        <div class="card overflow-hidden p-0 relative" slot-scope="{ filteredRows: rows }">

            <data-list-table>
                <template slot="cell-title" slot-scope="{ row: lock }">
                    <a :href="lock.show_url">{{ lock.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: lock, index }">
                    <dropdown-list v-if="lock.can_delete">
                        <dropdown-item
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${lock.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${lock.id}`"
                                :resource="lock"
                                @deleted="request">
                            </resource-deleter>
                        </dropdown-item>
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>

export default {

    mixins: [Listing],

    props: ['initialColumns'],

    data() {
        return {
            columns: this.initialColumns,
            requestUrl: cp_url('statamic-locks/locks'),
        }
    }

}
</script>
