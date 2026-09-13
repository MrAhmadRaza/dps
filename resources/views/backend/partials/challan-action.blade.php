
<div class="d-flex gap-2 justify-content-center">
 
     <!-- View Button -->
    <a href="{{ $viewRoute ?? '#!' }}"
       class="btn btn-sm d-flex align-items-center justify-content-center"
        style="width:36px;height:36px;color:#1572e8;border:1.5px solid #1572e8;border-radius:6px;background:transparent;"
       onmouseover="this.style.backgroundColor='#1572e8';this.style.color='#fff';"
       onmouseout="this.style.backgroundColor='transparent';this.style.color='#1572e8';"
       title="View">
          <i class="fa fa-eye"></i>
    </a>

    @if($status === 'review' || $status === 'approved')
      <!-- Status Badge -->
      @if($status === 'approved')
        <!-- View Button -->  
    
      <a href="{{ $pendingRoute ?? '#!' }}"
        class="btn btn-sm d-flex align-items-center justify-content-center"
          style="width:36px;height:36px;color:#1572e8;border:1.5px solid #1572e8;border-radius:6px;background:transparent;"
        onmouseover="this.style.backgroundColor='#1572e8';this.style.color='#fff';"
        onmouseout="this.style.backgroundColor='transparent';this.style.color='#1572e8';"
        title="Pending Challan">
          <i class="fa-solid fa-clock fa-lg"></i>
      </a>
      @else
          <a href="{{ $approvedRoute ?? '#!' }}"
              class="btn btn-sm d-flex align-items-center justify-content-center"
              style="width:36px;height:36px;color:#1572e8;border:1.5px solid #1572e8;border-radius:6px;background:transparent;"
              onmouseover="this.style.backgroundColor='#1572e8';this.style.color='#fff';"
              onmouseout="this.style.backgroundColor='transparent';this.style.color='#1572e8';"
              title="Approved Challan">
                <i class="fa-solid fa-circle-check fa-lg"></i>
          </a>
          
      @endif
    @endif

    <!-- Dropdown for Delete -->
   <form action="{{ $deleteRoute ?? '#!' }}" method="POST" class="d-inline">
        @csrf @method('DELETE')
        <button type="submit"
                onclick="return confirm('Are you sure you want to delete this challan?')"
                class="btn btn-sm d-flex align-items-center justify-content-center"
                style="width:36px;height:36px;color:#1572e8;border:1.5px solid #1572e8;border-radius:6px;background:transparent;"
                onmouseover="this.style.backgroundColor='#1572e8';this.style.color='#fff';"
                onmouseout="this.style.backgroundColor='transparent';this.style.color='#1572e8';"
                title="Delete Challan">
            <i class="fa fa-trash"></i>
        </button>
    </form>
</div>