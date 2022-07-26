export default {
    set data(data){
        this.data = data;
    },
    get data(){
        return this.data
    },
    update(data){
        this.data = {...this.data, ...data};
    },
    data: {}
}