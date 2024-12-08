Vue.component( 'list-users', {
  
    props: [ 'cmd', 'title-txt', 'search-txt' ],
    data(){
        return {
            userListData: []
        }
    },
    methods:{
        showUserList: function( json ){

            if( typeof json == 'undefined') return
    
            // Sort users by name
            var ordered = json.users.sort( ( a, b ) => {
                if( a.user > b.user ) return 1
                else if( a.user < b.user ) return -1
                return 0
            })
    
            this.userListDataFixed = json.users
            this.userListData = ordered
        },
        
        searchUsers: function(){
            var text = document.querySelector( '.search form input[name=search]' ).value

            if( text == '')
                this.userListData = this.userListDataFixed.concat()

            else{
                this.userListData = this.userListData.filter( ( item ) => {
                    if( item.user.match( new RegExp( text )) )
                        return true
                })

            }
        },

        onItemClick: function( user ){
            this.$emit( 'view-user',  user)
        },
    },
    mounted(){
        this.$props.cmd( 'listUsers', { types: "sudo|students|teachers" })
        .then( this.showUserList )
    },
    template: `<div>
                    <h4>{{ titleTxt }}</h4>
                    <div class='indexes'>
                        <div class='search'>
                            <form>
                                <input v-on:keyup='searchUsers' type='text' name='search' v-bind:placeholder='searchTxt'></input>
                            </form>
                        </div>
                    </div>
                    <div class='scroll1'>
                        <div v-for='user in userListData' class='userListItem pointer' v-on:click='onItemClick(user)'>
                            <span v-if='user.group == "students"' class='student'></span>
                            <span v-if='user.group == "teachers"' class='teacher'></span>
                            <span v-if='user.group == "sudo"' class='admin'></span>
                            <span class='user-text'>{{  user.user }}</span> 
                        </div>
                    </div>
                </div>`
})