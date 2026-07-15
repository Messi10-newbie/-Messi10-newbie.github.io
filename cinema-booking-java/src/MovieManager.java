import java.io.*;
import java.util.*;

public class MovieManager {

    private static final String FILE = "movies.txt";

    public static void initDefaults() {
        File f = new File(FILE);
        if (!f.exists()) {
            List<Movie> defaults = Arrays.asList(
                new Movie("Spider-Man: No Way Home", "Action/Adventure", "2h 28m", 15.00,
                          "Peter Parker's identity is revealed and he seeks help from Doctor Strange.", "PG-13"),
                new Movie("Avengers: Endgame", "Action/Sci-Fi", "3h 1m", 18.00,
                          "The Avengers assemble for a final stand against Thanos.", "PG-13"),
                new Movie("The Batman", "Action/Crime", "2h 56m", 14.00,
                          "Batman ventures into Gotham's criminal underworld.", "PG-13"),
                new Movie("Top Gun: Maverick", "Action/Drama", "2h 11m", 13.00,
                          "Maverick trains a new generation of Top Gun graduates.", "PG-13"),
                new Movie("Doctor Strange in the Multiverse of Madness", "Fantasy/Action", "2h 6m", 16.00,
                          "Doctor Strange travels into the Multiverse.", "PG-13")
            );
            save(defaults);
        }
    }

    public static List<Movie> loadAll() {
        List<Movie> list = new ArrayList<>();
        try (BufferedReader br = new BufferedReader(new FileReader(FILE))) {
            String line;
            while ((line = br.readLine()) != null) {
                if (!line.isBlank()) {
                    Movie m = Movie.fromFileString(line.trim());
                    if (m != null) list.add(m);
                }
            }
        } catch (IOException ignored) {}
        return list;
    }

    public static void save(List<Movie> movies) {
        try (PrintWriter pw = new PrintWriter(new FileWriter(FILE))) {
            for (Movie m : movies) pw.println(m.toFileString());
        } catch (IOException e) { e.printStackTrace(); }
    }

    public static void addMovie(Movie m) {
        List<Movie> list = loadAll();
        list.add(m);
        save(list);
    }

    public static void updateMovie(String originalTitle, Movie updated) {
        List<Movie> list = loadAll();
        for (int i = 0; i < list.size(); i++) {
            if (list.get(i).getTitle().equals(originalTitle)) {
                list.set(i, updated);
                break;
            }
        }
        save(list);
        if (!originalTitle.equals(updated.getTitle())) {
            ShowtimeManager.updateMovieTitle(originalTitle, updated.getTitle());
        }
    }

    public static void deleteMovie(String title) {
        List<Movie> list = loadAll();
        list.removeIf(m -> m.getTitle().equals(title));
        save(list);
    }

    public static Movie findByTitle(String title) {
        return loadAll().stream().filter(m -> m.getTitle().equals(title)).findFirst().orElse(null);
    }
}
