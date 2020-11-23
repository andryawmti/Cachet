<script>
module.exports = {
    computed: {
        showComponentStatus() {
            const list = this.components.filter(id => id != '')
            return list.length
        },
    },
    data () {
        return {
            template: null,
            name: '',
            status: null,
            visible: 1,
            sticky: 0,
            message: '',
            when: null,
            notify: false,
            components: [],
            component_status: null,
        }
    },
    methods: {
        getTemplate (template) {
            axios.get('/dashboard/api/incidents/templates', {
                params: {
                    slug: template
                }
            }).then(response => {
                this.name = response.data.name
                this.message = response.data.template
            }).catch(response => {
                (new Cachet.Notifier()).notify('There was an error finding that template.');
            })
        }
    },
    watch: {
        'template' (template) {
            this.getTemplate(template)
        },
    }
}
</script>
