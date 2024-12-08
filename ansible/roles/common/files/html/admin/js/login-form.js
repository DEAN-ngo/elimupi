Vue.component('login-form', {
        props: [ 'cmd', 'title', 'username', 'placeholder_username', 'password', 'placeholder_password', 'submit', 'errorMsg' ],
        data: function(){ return {}},
        methods: {
            showErrorMsg: function( msg ){
                Vue.nextTick( () => { this.$props.errorMsg = msg } )
            },
            doLogin: function( ){
                this.$emit('form-submit' )

                this.$props.cmd( 'login', null, this.showErrorMsg, event )
                .catch( () => {})
                .then( ( json ) => { if ( typeof json != 'undefined' ) this.$emit( 'valid-login', json ) })
            }
        },
        template: `<div class="enter-credentials login">

                    <div class="errorMsg">{{ errorMsg }}</div>

                    <div class="content">
                        <div class="text">
                            {{title}} 
                        </div>
                        <form action="?" class="login" v-on:submit.prevent="doLogin">
                            <div class="field">
                                <input type="text" name="user" required 
                                    v-bind:placeholder="placeholder_username"
                                    v-bind:value="username" >
                                <label></label>
                            </div>
                            <div class="field">
                                <input type="password" name="password" 
                                    v-bind:placeholder="placeholder_password" 
                                    autocomplete="new-password"
                                    v-bind:value="password"/>
                                <label></label>
                            </div>
                            <br>
                            <button>{{submit}}</button>
                        </form>
                    </div>
                </div>`
    })