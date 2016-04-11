
    <form action="{{ route('private.document.share') }}" method="post" id="share-form">
        <div class="col-md-12">
                <div id="share-msg"></div>
                <div class="section-title-wr">
                    <h3 class="section-title left"><span>Share Document</span></h3>
                </div>
                
                    <div class="form-group">
                        <label>Send this document to</label>
                        <input type="email" name="email" placeholder="email" />
                    </div>
                    <div class="form-group">
                        <label>Message (optional)</label>
                        <textarea class="form-control" name="message"  placeholder="Message (optional)" style="height:100px;"></textarea>
                    </div>
                
                    <div class="form-group">
                        <label>Set password (optional)</label>
                        <input type="password" name="password"  />
                    </div>
                    
                    <div class="form-group">
                        <label>Set expiration</label>
                        <select name="expires">
                            <option value="never">Never</option>
                            <option value="24hrs">24 Hours</option>
                            <option value="1week">1 week</option>
                            <option value="1month">1 month</option>
                        </select>
                    </div>
                    
                    <input type="hidden" name="fileId" value="{!! $file->id !!}" />
                    <button  class="btn btn-base submit"  >Send</button>
            </div>
        </form>
