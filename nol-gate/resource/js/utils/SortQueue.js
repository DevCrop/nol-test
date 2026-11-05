/** Keep rapid clicks ordered; refresh only after all accepted clicks finish. */
export class SortQueue {
  constructor(send,complete,error) {this.send=send;this.complete=complete;this.error=error;this.pending=0;this.tail=Promise.resolve();this.failed=false;}
  enqueue(id,direction) {
    this.pending++;
    this.tail=this.tail.then(()=>{if(!this.failed)return this.send(id,direction);})
      .catch(error=>{this.failed=true;this.error(error);})
      .finally(()=>{this.pending--;if(!this.pending)this.complete();});
    return this.tail;
  }
}
