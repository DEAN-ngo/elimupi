Vue.component( 'moodle-ldap', {
    props: ['cmd', 'submit-txt'],
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
            this.$props.cmd('installMoodleLDAPPlugin')
            .catch( this.fail )
            .then( ( json ) => {
                if( typeof json != 'undefined'){
                    var a = this.$el.querySelector('span')
                    a.innerHTML = json['msg']
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
                <span/>
                <button v-on:click.stop='start'>{{ submitTxt }}</button>
            </div>`
})