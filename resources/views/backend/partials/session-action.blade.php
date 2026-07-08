
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
    
    <!-- Edit Button -->
    <a href="{{ $editRoute ?? '#!' }}"
       class="btn btn-sm d-flex align-items-center justify-content-center"
        style="width:36px;height:36px;color:#1572e8;border:1.5px solid #1572e8;border-radius:6px;background:transparent;"
       onmouseover="this.style.backgroundColor='#1572e8';this.style.color='#fff';"
       onmouseout="this.style.backgroundColor='transparent';this.style.color='#1572e8';"
       title="Edit">
        <i class="fa fa-pencil-alt"></i>
    </a>

    <!-- Dropdown for Delete -->
   <form action="{{ $deleteRoute ?? '#!' }}" method="POST" class="d-inline">
        @csrf @method('DELETE')
        <button type="submit"
                onclick="return confirm('Are you sure you want to delete this record?')"
                class="btn btn-sm d-flex align-items-center justify-content-center"
                style="width:36px;height:36px;color:#1572e8;border:1.5px solid #1572e8;border-radius:6px;background:transparent;"
                onmouseover="this.style.backgroundColor='#1572e8';this.style.color='#fff';"
                onmouseout="this.style.backgroundColor='transparent';this.style.color='#1572e8';"
                title="Delete">
            <i class="fa fa-trash"></i>
        </button>
    </form>
</div>