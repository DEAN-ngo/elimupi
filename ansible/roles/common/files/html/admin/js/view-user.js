Vue.component( 'view-user', {

    props: [ 'cmd', 'user', 'title-txt' ],


    data(){
        return {
            msg: ''
        }
    },

    methods: {
        removeUser(){
            this.$props.cmd('deleteUser', {userName: this.user.user})
            .catch( ( err ) => { this.msg = err })
            .then( ( json ) => {
                if( typeof json != 'undefined'){
                    this.msg = json.msg
                    this.user = null
                }
            })
        },
        reset(user){
          this.$emit( 'reset-password', user ) 
        },
    },

    template:   `<div class='view-user'>
                    <div><h1>{{ titleTxt }}</h1></div>
                    <div class='msg'>{{ msg }}</div>
                    <div v-if='user'>{{ user.user }} : {{ user.group }} 
                        <a class='pointer icon after' v-on:click='removeUser'><img src='img/cross.svg'/></a>
                        <a class='pointer icon after' v-on:click='reset(user)'><img src='img/key.svg'/></a>
                    </div>
                </div>`
} )