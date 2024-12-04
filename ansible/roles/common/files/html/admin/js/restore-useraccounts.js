Vue.component( 'restore-useraccounts', {
    props: ['cmd', 'submit-txt'],
    data(){
        return {
            result: '',
        }
    },
    methods:{
        upload(){
            var f = this.$el.querySelector('form'),
            params = {};
    
            params['backup'] = f.querySelector('input[name=backup]').files[0]
    
            this.$props.cmd('restoreBackup', params)
            .then( ( json ) => {
                if( typeof json != 'undefined' ){
                    this.$el.querySelector( 'form' ).classList.add( 'hide' )
                    this.result = json.msg
                }
            })
        },
    },
    template: `<div>
                <span>{{ result }}</span>
                <form class='columns' v-on:submit.prevent="upload">
                    <input type='file' name='backup'></input>
                    <button>{{submitTxt}}</button>
                </form>
            </div>`
})