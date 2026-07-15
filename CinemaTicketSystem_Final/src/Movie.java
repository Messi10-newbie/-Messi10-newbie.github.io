import java.util.Objects;

public class Movie {
    private String title;
    private String year;
    private String description;
    private String posterPath;

    public Movie(String title, String year, String description) {
        this(title, year, description, "");
    }

    public Movie(String title, String year, String description, String posterPath) {
        this.title       = title;
        this.year        = year;
        this.description = description;
        this.posterPath  = posterPath;
    }

    public String getTitle()       { return title; }
    public String getYear()        { return year; }
    public String getDescription() { return description; }
    public String getPosterPath()  { return posterPath; }

    public void setTitle(String title)             { this.title = title; }
    public void setYear(String year)               { this.year = year; }
    public void setDescription(String description) { this.description = description; }
    public void setPosterPath(String posterPath)   { this.posterPath = posterPath; }

    @Override
    public String toString() {
        return title + " (" + year + ") - Desc: " + description;
    }

    @Override
    public int hashCode() { return Objects.hash(title, year); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof Movie)) return false;
        Movie other = (Movie) obj;
        return title.equals(other.title) && year.equals(other.year);
    }
}
