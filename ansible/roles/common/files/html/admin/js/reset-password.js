Vue.component( 'reset-password', {

    props: [ 'cmd', 'user', 'is-valid-password', 'title-txt', 'new-password-txt', 'new-password-again-txt', 'account-txt', 'submit-txt', 'is-admin' ],

    data(){
        return {
            validPassword: false,
            passwordTwiceOk: false
        }
    },

    methods:{
        keyUp(){

            return  this.checkPasswords()
        },

        checkPasswords(){
            var password1 = this.$el.querySelector( ' form input[name=password1]' ).value,
                password2 = this.$el.querySelector( ' form input[name=password2]' ).value

            if( this.$props.isValidPassword( password1 )){
                if( password1 != password2 || password1.length == 0 ) {
                    this.passwordTwiceOk = false
                    this.validPassword = true
                }
                else{
                    this.passwordTwiceOk = true
                }
            }
            else{
                this.passwordTwiceOk = false
                this.validPassword = false
            }
        },

        populateUsersData(){
            var types = 'students'
            if( this.isAdmin )
                types += '|teachers'

            this.$props.cmd( 'listUsers', { types: types} )
            .then( ( json ) => {
                var el = this.$el.querySelector( '#usersData' )
                el.innerHTML = ''
                if( typeof json != 'undefined'){
                    if(json.users && json.users.length > 0)
                        json.users.forEach( ( user ) => {
                            var opt = document.createElement( 'option' )
                            opt.setAttribute( 'value', user.user )
                            el.appendChild( opt )
                        })
                }
            })
        },

        resetPassword(){
            if( this.validPassword ){
                var password1 = this.$el.querySelector('input[name=password1]').value,
                    name = this.$el.querySelector('input[name=userUser]').value

                this.$props.cmd( 'resetPassword', { forUser: name, newPassword: password1 })
                .catch( ( err ) => {
                    this.$el.querySelector('.errorMsg').innerHTML = err
                })
                .then( ( json ) => {
                    if( typeof json != 'undefined'){
                        if( json.ok ){
                            this.$el.querySelector('.errorMsg').innerHTML = json.msg
                        }
                        else
                            this.$el.querySelector('.errorMsg').innerHTML = json.msg
                    }
                })
            }
        }
    },

    mounted(){
        this.populateUsersData()
    },

    template: `<div class='enter-credentials forUser'>
                <div class='errorMsg'></div>

                <div class="content">
                    <div class="text">{{ titleTxt }}</div>
                    <form v-on:submit.prevent='resetPassword'>
                        <div class="field">
                            <input list='usersData' type="text" name='userUser' required 
                                v-bind:placeholder='accountTxt' :value='user && user.user? user.user : ""'> 
                            <label></label>
                            <datalist id="usersData"></datalist>
                        </div>
                        <div class="field">
                            <input type="password" name='password1' required 
                                v-on:keyup='keyUp'
                                v-bind:placeholder='newPasswordTxt'> 
                                <img v-if='! validPassword' class='icon right' src='img/cross.svg' />
                            <label></label>
                        </div>
                        <div class="field">
                            <input type="password" name='password2' required
                                v-on:keyup='checkPasswords(".forUser")'
                                v-bind:placeholder='newPasswordAgainTxt'>
                            <img v-if='passwordTwiceOk' class='icon right' src='img/check.svg' />
                            <label></label>
                        </div>
                        <br>
                        <button>{{ submitTxt }}</button>
                    </form>
                </div>
            </div>`
})