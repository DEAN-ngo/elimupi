Vue.component( 'change-password', {
    
    props: [
            'cmd',
            'is-valid-password',  
            'do-change-password', 
            'new-password-txt', 
            'password-again', 
            'submit-txt', 
            'new-password'],

    data(){
        return {
            validPassword: false,
            passwordTwiceOk: false
        }
    },

    methods: {
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

        changePassword(){
            if( this.validPassword ){
                var password1 = this.$el.querySelector('input[name=password1]').value
                this.$props.cmd( 'changePassword', { newPassword: password1 } )
                .catch( ( err ) => {
                    this.$el.querySelector('.errorMsg').innerHTML = err
                })
                .then( ( json ) => {
                    if( typeof json != 'undefined'){
                        if( json.ok ){
                            this.$emit("password-change", password1 )
                            this.$el.querySelector('.errorMsg').innerHTML = json.msg
                        }
                        else
                            this.$el.querySelector('.errorMsg').innerHTML = json.msg
                    }
                })
            }
        }
    },

    template:   `<div>
                    <div class='enter-credentials twice'>
                        <div class='errorMsg'></div>

                        <div class="content">
                            <div class="text">
                                {{ newPasswordTxt }}
                            </div>
                            <form v-on:submit.prevent='changePassword'>
                                <div class="field">
                                    <input type="password" name='password1' required 
                                        v-on:keyup='keyUp'
                                        v-bind:placeholder='newPasswordTxt'> </input>
                                        <img v-if='! validPassword' class='icon right' src='img/cross.svg' />
                                    <label></label>
                                </div>
                                <div class="field">
                                    <input type="password" name='password2' required
                                        v-on:keyup='keyUp'
                                        v-bind:placeholder='passwordAgain'></input>
                                    <img v-if='passwordTwiceOk' class='icon right' src='img/check.svg' />
                                    <label></label>
                                </div>
                                <br>
                                <button>{{ submitTxt }}</button>
                            </form>
                        </div>
                    </div>
                </div>`
} )