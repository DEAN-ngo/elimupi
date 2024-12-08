Vue.component( 'secondary-disk', {
    props: ['cmd','list-packages','create-btn-txt','copy-packages-txt'],
    data(){
        return {
        }
    },
    methods: {
        createDisk(){
            this.init()
            var progress = this.$el.querySelector('progress')
            progress.removeAttribute('value')
            this.$props.cmd()
        },
        end(){
            var progress = this.$el.querySelector('progress')
            progress.remove()
        },
        init(){
            if( ! this.$el.querySelector('progress') ){
                var p = document.createElement('progress')
                this.$el.prepend(p)
            }
        },
        fail(){
            var progress = this.$el.querySelector('progress')
            progress.value = 0
        }
    },
    template: `<div>
                <progress max='100' value='0'/>
                <button v-on:click.stop='listPackages'>{{copyPackagesTxt}}</button>
                <button v-on:click.stop='createDisk'>{{ createBtnTxt }}</button>
            </div>`
}) 
