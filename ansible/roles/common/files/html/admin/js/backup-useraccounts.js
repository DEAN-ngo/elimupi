Vue.component( 'backup-useraccounts', {
    props: ['cmd', 'submit-txt', 'download-txt'],
    data(){
        return {
        }
    },
    methods: {
        start(){
            this.init()
            this.$el.querySelector('button').classList.add('hide')
            var progress = this.$el.querySelector('progress')
            progress.removeAttribute('value')
            this.$props.cmd('createBackup')
            .catch( this.fail )
            .then( ( json ) => {
                if( typeof json != 'undefined'){
                    var a = this.$el.querySelector('a')
                    a.setAttribute( 'href', document.location.protocol + '//' + json.msg )
                    a.innerHTML = this.$props['downloadTxt']
                }
                this.end()
            })
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
            this.$el.querySelector('button').classList.remove('hide')
            var progress = this.$el.querySelector('progress')
            progress.value = 0
        }
    },
    template: `<div>
                <button v-on:click.stop='start'>{{ submitTxt }}</button>
                <a download></a>
            </div>`
})