Vue.component( 'add-user', {
    props: [ 'cmd', 'is-valid-password', 'title-txt', 'name-txt', 'enter-password-txt', 'enter-password-again-txt', 'type-student-txt', 'type-teacher-txt', 'submit-txt' ],

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
                    this.validPassword = false
                }
                else{
                    this.passwordTwiceOk = true
                    this.validPassword = true
                }
            }
            else{
                this.passwordTwiceOk = false
                this.validPassword = false
            }
        },

        addUser(){
            if( this.validPassword ){
                var password1 = this.$el.querySelector('input[name=password1]').value,
                    name = this.$el.querySelector('input[name=userName]').value,
                    type = this.$el.querySelector('select[name=userType]').value

                this.$props.cmd( 'addUser', { userName: name, type: type, newPassword: password1 })
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

    template: `<div>
                    <div class='enter-credentials addUser'>

                        <div class='errorMsg'></div>

                        <div class='content'>
                            <div class='text'>{{ titleTxt }}</div>
                            <form v-on:submit.prevent='addUser'>
                                <div class='field'>
                                    <input type='text' name='userName' required placeholder=''/>
                                    <label></label>
                                </div>
                                <div class='field'>
                                    <input 
                                        required
                                        type='password' 
                                        name='password1'
                                        v-on:keyup='keyUp' 
                                        v-bind:placeholder='enterPasswordTxt'/>
                                        <img v-if='! validPassword' class='icon right' src='img/cross.svg' />
                                    <label></label>
                                </div>
                                <div class='field'>
                                    <input 
                                        required
                                        type='password' 
                                        name='password2' 
                                        v-on:keyup='keyUp'
                                        v-bind:placeholder='enterPasswordAgainTxt'/>
                                    <img v-if='passwordTwiceOk' class='icon right' src='img/check.svg' />
                                    <label></label>
                                </div>
                                <div class='field'>
                                    <select id='userType' name='userType'>
                                        <option value='students'>{{ typeStudentTxt }}</option>
                                        <option value='teachers'>{{ typeTeacherTxt }}</option>
                                    </select>
                                    <label></label>
                                </div>
                                <br>
                                <button>{{ submitTxt }}</button>
                            </form>
                        </div>
                    </div>
            </div>`
} )