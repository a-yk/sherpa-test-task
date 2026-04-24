import { Component } from '@angular/core';
import { RouterLink } from "@angular/router";
import { environment } from '../../environment/environment';

import { HttpClient, HttpHeaders } from '@angular/common/http';
@Component({
  selector: 'app-import',
  imports: [
    RouterLink,
  ],
  templateUrl: './import.html',
})
export class ImportPage {
  selectedFile: File | null = null;
  backendUrl = environment.backendUrl;

  constructor(private http: HttpClient) {}

  onFileSelected(event: any) {
    this.selectedFile = event.target.files[0];
  }

  uploadFile() {
    if (!this.selectedFile) return;

    const formData = new FormData();
    formData.append('import', '');
    formData.append('import[file_name]', this.selectedFile, this.selectedFile.name);

    const headers = new HttpHeaders();

    this.http.post(`${this.backendUrl}api/import`, formData, { headers, responseType: 'text' })
      .subscribe({
        next: (response) => {
          alert('Файл успешно загружен. Каталог обновится в ближайшее время.');
        },
        error: (error) => {
          alert('Ошибка при загрузке файла: ' + error.message);
        }
      });
  }
}
